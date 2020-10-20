# Unique Model Symfony Constraint [![Build Status](https://travis-ci.com/BoShurik/unique-model-constraint.svg?branch=master)](https://travis-ci.com/BoShurik/unique-model-constraint)

Port of `UniqueEntity` constraint

## Symfony integration

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    BoShurik\Constraints\Model\UniqueModelValidator: ~
```

without `autoconfigure`:

```yaml
services:
    BoShurik\Constraints\Model\UniqueModelValidator:
        arguments:
            - '@doctrine' # @doctrine_mongodb
        tags: ['validator.constraint_validator']
```

