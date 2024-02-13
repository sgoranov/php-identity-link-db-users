<?php

namespace App\Service\Validator;

use Symfony\Component\Validator\Constraint;

class GroupId extends Constraint
{
    public string $message = 'Invalid group id.';
}