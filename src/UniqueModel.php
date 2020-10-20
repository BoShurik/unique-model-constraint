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

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class UniqueModel extends Constraint
{
    public const NOT_UNIQUE = 'b952342b-6295-4ae8-8058-69a9f5e93fd1';

    protected static $errorNames = [
        self::NOT_UNIQUE => 'NOT_UNIQUE',
    ];

    public $class;
    public $fields;
    public $identifier;
    public $nullable = false;
    public $repositoryMethod;
    public $message = 'This value is already used.';

    public function __construct($class, array $fields = null, ?string $identifier = null, bool $nullable = null, ?string $repositoryMethod = null, string $message = null, array $options = [])
    {
        if (\is_array($class)) {
            $options = array_merge($class, $options);
        } else {
            $options['class'] = $class;
            $options['fields'] = $fields;
        }

        parent::__construct($options);

        $this->fields = $fields ?? $this->fields;
        $this->identifier = $identifier ?? $this->identifier;
        $this->nullable = $nullable ?? $this->nullable;
        $this->repositoryMethod = $repositoryMethod ?? $this->repositoryMethod;
        $this->message = $message ?? $this->message;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getRequiredOptions()
    {
        return ['class', 'fields'];
    }
}
