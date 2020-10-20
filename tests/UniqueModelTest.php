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

use BoShurik\Constraints\Model\UniqueModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class UniqueModelTest extends TestCase
{
    public function testTarget()
    {
        $constraint = new UniqueModel('stdClass', [
            'field' => 'field',
        ]);

        $this->assertSame($constraint->getTargets(), Constraint::CLASS_CONSTRAINT);
    }
}
