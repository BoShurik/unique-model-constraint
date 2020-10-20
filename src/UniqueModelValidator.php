<?php

/*
 * This file is part of the unique-model-constraint.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\Constraints\Model;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueModelValidator extends ConstraintValidator
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueModel) {
            throw new UnexpectedTypeException($constraint, UniqueModel::class);
        }
        if (!\is_object($value)) {
            throw new UnexpectedTypeException($value, 'object');
        }
        if (0 === \count($constraint->fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        $objectManager = $this->registry->getManagerForClass($constraint->class);

        if (!$objectManager) {
            throw new ConstraintDefinitionException(sprintf('Unable to find the object manager associated with an entity of class "%s".', $constraint->class));
        }

        $class = $objectManager->getClassMetadata($constraint->class);
        $criteria = [];

        /** @var string $fieldName */
        foreach ($constraint->fields as $fieldName) {
            if (!$class->hasField($fieldName) && !$class->hasAssociation($fieldName)) {
                throw new ConstraintDefinitionException(sprintf('The field "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.', $fieldName));
            }

            if ($constraint->nullable && $value->$fieldName === null) {
                continue;
            }

            $criteria[$fieldName] = $value->$fieldName;

            if (null !== $criteria[$fieldName] && $class->hasAssociation($fieldName)) {
                /* Ensure the Proxy is initialized before using reflection to
                 * read its identifiers. This is necessary because the wrapped
                 * getter methods in the Proxy are being bypassed.
                 */
                $objectManager->initializeObject($criteria[$fieldName]);
            }
        }

        if ($criteria === []) {
            return;
        }

        $repository = $objectManager->getRepository($constraint->class);

        if ($constraint->repositoryMethod) {
            /** @var array|iterable|\Traversable $result */
            $result = $repository->{$constraint->repositoryMethod}($criteria);
        } else {
            $result = $repository->findBy($criteria);
        }

        if ($result instanceof \IteratorAggregate) {
            $result = $result->getIterator();
        }

        /* If the result is a MongoCursor, it must be advanced to the first
         * element. Rewinding should have no ill effect if $result is another
         * iterator implementation.
         */
        if ($result instanceof \Iterator) {
            if (iterator_count($result) === 0) {
                return;
            }

            $result->rewind();
        } elseif (\is_array($result)) {
            reset($result);

            if (\count($result) === 0) {
                return;
            }
        }

        $identifierField = $constraint->identifier;
        if ($identifierField && $identifierValue = $value->$identifierField) {
            $filterIdentifiers = array_filter(\is_array($result) ? $result : iterator_to_array($result), function (object $value) use ($class, $identifierValue) {
                /** @var mixed $id */
                $id = current($class->getIdentifierValues($value));

                return $id !== $identifierValue;
            });

            if (0 === \count($filterIdentifiers)) {
                return;
            }

            $result = $filterIdentifiers;
        }

        foreach ($constraint->fields as $modelField => $objectField) {
            $this->context->buildViolation($constraint->message)
                ->setCode(UniqueModel::NOT_UNIQUE)
                ->setCause($result)
                ->setInvalidValue(implode(', ', $criteria))
                ->atPath($modelField)
                ->addViolation()
            ;
        }
    }
}
