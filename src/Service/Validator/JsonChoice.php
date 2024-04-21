<?php

namespace App\Service\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class JsonChoice extends Constraint
{
    public string $message = 'The value "{{ invalidChoices }}" is not a valid choice.';
    public array $choices;

    public function __construct(array $choices, mixed $options = null, array $groups = null)
    {
        parent::__construct($options, $groups);
        $this->choices = $choices;
    }
}