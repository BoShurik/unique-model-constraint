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

use Doctrine\Persistence\ObjectRepository;

class TestObjectRepository implements ObjectRepository
{
    /**
     * @var callable
     */
    private $criteriaTest;

    /**
     * @var array|\Traversable
     */
    private $result;

    public function __construct(callable $criteriaTest, $result)
    {
        $this->criteriaTest = $criteriaTest;
        $this->result = $result;
    }

    public function customMethod(array $criteria)
    {
        \call_user_func($this->criteriaTest, $criteria, __FUNCTION__);

        return $this->result;
    }

    public function find($id)
    {
        throw new \LogicException('Not implemented');
    }

    public function findAll()
    {
        throw new \LogicException('Not implemented');
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        \call_user_func($this->criteriaTest, $criteria, __FUNCTION__);

        return $this->result;
    }

    public function findOneBy(array $criteria)
    {
        throw new \LogicException('Not implemented');
    }

    public function getClassName()
    {
        throw new \LogicException('Not implemented');
    }
}
