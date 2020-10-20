<?php

/*
 * This file is part of the unique-model-constraint.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\Constraints\Model\Tests;

use BoShurik\Constraints\Model\Tests\Fixtures\TestClassMetadata;
use BoShurik\Constraints\Model\Tests\Fixtures\TestObjectRepository;
use BoShurik\Constraints\Model\UniqueModel;
use BoShurik\Constraints\Model\UniqueModelValidator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @see https://github.com/symfony/symfony/blob/5.x/src/Symfony/Bridge/Doctrine/Validator/Constraints/UniqueEntityValidator.php
 */
class UniqueModelValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var ManagerRegistry|MockObject
     */
    private $registry;

    protected function createValidator()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);

        return new UniqueModelValidator($this->registry);
    }

    public function testConstraintIsUniqueModel()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(null, new class() extends Constraint {
        });
    }

    public function testValueIsObject()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('string', new UniqueModel([
            'class' => 'class',
            'fields' => [],
        ]));
    }

    public function testFieldsIsNotEmpty()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->validator->validate(new \stdClass(), new UniqueModel([
            'class' => 'stdClass',
            'fields' => [],
        ]));
    }

    public function testValidManager()
    {
        $this->registry
            ->method('getManagerForClass')
            ->with('stdClass')
            ->willReturn(null)
        ;

        $this->expectException(ConstraintDefinitionException::class);

        $this->validator->validate(new \stdClass(), new UniqueModel([
            'class' => 'stdClass',
            'fields' => [
                'no' => 'no',
            ],
        ]));
    }

    public function testNotMappedFields()
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($this->createClassMetadata())
        ;

        $this->registry
            ->method('getManagerForClass')
            ->with('stdClass')
            ->willReturn($manager)
        ;

        $this->expectException(ConstraintDefinitionException::class);

        $this->validator->validate(new \stdClass(), new UniqueModel([
            'class' => 'stdClass',
            'fields' => [
                'no' => 'no',
            ],
        ]));
    }

    public function testNullable()
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($this->createClassMetadata())
        ;

        $this->registry
            ->method('getManagerForClass')
            ->with('stdClass')
            ->willReturn($manager)
        ;

        $value = new \stdClass();
        $value->field = null;
        $value->association = null;

        $this->validator->validate($value, new UniqueModel([
            'class' => 'stdClass',
            'fields' => [
                'field' => 'field',
                'association' => 'association',
            ],
            'nullable' => true,
        ]));

        $this->assertNoViolation();
    }

    public function testCustomRepositoryMethod()
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($this->createClassMetadata())
        ;

        $manager
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->createRepository([
                'field' => 'field',
                'association' => 'association',
            ], [], 'customMethod'))
        ;

        $this->registry
            ->method('getManagerForClass')
            ->with('stdClass')
            ->willReturn($manager)
        ;

        $value = new \stdClass();
        $value->field = 'field';
        $value->association = 'association';

        $this->validator->validate($value, new UniqueModel([
            'class' => 'stdClass',
            'fields' => [
                'field' => 'field',
                'association' => 'association',
            ],
            'nullable' => true,
            'repositoryMethod' => 'customMethod',
        ]));

        $this->assertNoViolation();
    }

    /**
     * @dataProvider emptyRepositoryResultProvider
     *
     * @param mixed $repositoryResult
     */
    public function testEmptyRepositoryResult($repositoryResult)
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($this->createClassMetadata())
        ;

        $manager
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->createRepository([
                'field' => 'field',
                'association' => 'association',
            ], $repositoryResult))
        ;

        $this->registry
            ->method('getManagerForClass')
            ->with('stdClass')
            ->willReturn($manager)
        ;

        $value = new \stdClass();
        $value->field = 'field';
        $value->association = 'association';

        $this->validator->validate($value, new UniqueModel([
            'class' => 'stdClass',
            'fields' => [
                'field' => 'field',
                'association' => 'association',
            ],
            'nullable' => true,
        ]));

        $this->assertNoViolation();
    }

    /**
     * @dataProvider repositoryResultProvider
     *
     * @param mixed $repositoryResult
     */
    public function testSameRepositoryResult($repositoryResult)
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($this->createClassMetadata())
        ;

        $manager
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->createRepository([
                'field' => 'field',
                'association' => 'association',
            ], $repositoryResult))
        ;

        $this->registry
            ->method('getManagerForClass')
            ->with('stdClass')
            ->willReturn($manager)
        ;

        $value = new \stdClass();
        $value->id = 'id';
        $value->field = 'field';
        $value->association = 'association';

        $this->validator->validate($value, new UniqueModel([
            'class' => 'stdClass',
            'fields' => [
                'field' => 'field',
                'association' => 'association',
            ],
            'identifier' => 'id',
            'nullable' => true,
        ]));

        $this->assertNoViolation();
    }

    /**
     * @dataProvider repositoryResultProvider
     *
     * @param mixed $repositoryResult
     */
    public function testInvalid($repositoryResult)
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($this->createClassMetadata())
        ;

        $manager
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->createRepository([
                'field' => 'field',
                'association' => 'association',
            ], $repositoryResult))
        ;

        $this->registry
            ->method('getManagerForClass')
            ->with('stdClass')
            ->willReturn($manager)
        ;

        $value = new \stdClass();
        $value->id = 'id';
        $value->field = 'field';
        $value->association = 'association';

        $this->validator->validate($value, new UniqueModel([
            'class' => 'stdClass',
            'fields' => [
                'field' => 'field',
                'association' => 'association',
            ],
            'nullable' => true,
            'message' => 'message',
        ]));

        $this->buildViolation('message')
            ->setCode(UniqueModel::NOT_UNIQUE)
            ->setInvalidValue('field, association')
            ->atPath('property.path.field')
            ->setCause($repositoryResult)
            ->buildNextViolation('message')
            ->setCode(UniqueModel::NOT_UNIQUE)
            ->setInvalidValue('field, association')
            ->atPath('property.path.association')
            ->setCause($repositoryResult)
            ->assertRaised()
        ;
    }

    /**
     * @dataProvider repositoryResultProvider
     *
     * @param mixed $repositoryResult
     */
    public function testInvalidWithId($repositoryResult)
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($this->createClassMetadata())
        ;

        $manager
            ->method('getRepository')
            ->with('stdClass')
            ->willReturn($this->createRepository([
                'field' => 'field',
                'association' => 'association',
            ], $repositoryResult))
        ;

        $this->registry
            ->method('getManagerForClass')
            ->with('stdClass')
            ->willReturn($manager)
        ;

        $value = new \stdClass();
        $value->id = 'no';
        $value->field = 'field';
        $value->association = 'association';

        $this->validator->validate($value, new UniqueModel([
            'class' => 'stdClass',
            'fields' => [
                'field' => 'field',
                'association' => 'association',
            ],
            'identifier' => 'id',
            'nullable' => true,
            'message' => 'message',
        ]));

        $this->buildViolation('message')
            ->setCode(UniqueModel::NOT_UNIQUE)
            ->setInvalidValue('field, association')
            ->atPath('property.path.field')
            ->setCause($repositoryResult instanceof \Iterator ? iterator_to_array($repositoryResult) : $repositoryResult)
            ->buildNextViolation('message')
            ->setCode(UniqueModel::NOT_UNIQUE)
            ->setInvalidValue('field, association')
            ->atPath('property.path.association')
            ->setCause($repositoryResult instanceof \Iterator ? iterator_to_array($repositoryResult) : $repositoryResult)
            ->assertRaised()
        ;
    }

    public function emptyRepositoryResultProvider()
    {
        yield [[]];

        yield [new \ArrayIterator([])];

        yield [new class() implements \IteratorAggregate {
            public function getIterator()
            {
                return new \ArrayIterator([]);
            }
        }];
    }

    public function repositoryResultProvider()
    {
        $result = new \stdClass();
        $result->id = 'id';

        yield [[$result]];

        yield [new \ArrayIterator([$result])];
    }

    private function createClassMetadata(): ClassMetadata
    {
        return new TestClassMetadata(['id'], ['field'], ['association']);
    }

    private function createRepository(array $with = [], $return = [], string $method = 'findBy')
    {
        return new TestObjectRepository(
            function (array $criteria, string $calledMethod) use ($with, $method) {
                $this->assertSame($with, $criteria);
                $this->assertSame($method, $calledMethod);
            },
            $return
        );
    }
}
