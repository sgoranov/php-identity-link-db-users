<?php

namespace App\Service\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UniqueEntry extends Constraint
{
    public string $message = 'The value "{{ value }}" already exists.';
}