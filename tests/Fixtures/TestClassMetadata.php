<?php

/*
 * This file is part of the unique-model-constraint.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\Constraints\Model\Tests\Fixtures;

use Doctrine\Persistence\Mapping\ClassMetadata;

class TestClassMetadata implements ClassMetadata
{
    private array $identifiers;
    private array $fields;
    private array $associations;

    public function __construct(array $identifiers, array $fields, array $associations)
    {
        $this->identifiers = $identifiers;
        $this->fields = $fields;
        $this->associations = $associations;
    }

    public function getName()
    {
        throw new \LogicException('Not implemented');
    }

    public function getIdentifier()
    {
        throw new \LogicException('Not implemented');
    }

    public function getReflectionClass()
    {
        throw new \LogicException('Not implemented');
    }

    public function isIdentifier($fieldName)
    {
        throw new \LogicException('Not implemented');
    }

    public function hasField($fieldName)
    {
        return \in_array($fieldName, $this->fields);
    }

    public function hasAssociation($fieldName)
    {
        return \in_array($fieldName, $this->associations);
    }

    public function isSingleValuedAssociation($fieldName)
    {
        throw new \LogicException('Not implemented');
    }

    public function isCollectionValuedAssociation($fieldName)
    {
        throw new \LogicException('Not implemented');
    }

    public function getFieldNames()
    {
        throw new \LogicException('Not implemented');
    }

    public function getIdentifierFieldNames()
    {
        throw new \LogicException('Not implemented');
    }

    public function getAssociationNames()
    {
        throw new \LogicException('Not implemented');
    }

    public function getTypeOfField($fieldName)
    {
        throw new \LogicException('Not implemented');
    }

    public function getAssociationTargetClass($assocName)
    {
        throw new \LogicException('Not implemented');
    }

    public function isAssociationInverseSide($assocName)
    {
        throw new \LogicException('Not implemented');
    }

    public function getAssociationMappedByTargetField($assocName)
    {
        throw new \LogicException('Not implemented');
    }

    public function getIdentifierValues($object)
    {
        return [$object->id];
    }
}
