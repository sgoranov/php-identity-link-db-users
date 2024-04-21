<?php

namespace App\Service\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JsonChoiceValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!is_array($value)) {
            return;
        }

        $invalidChoices = array_diff($value, $constraint->choices);
        if (!empty($invalidChoices)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ invalidChoices }}', implode(', ', $invalidChoices))
                ->addViolation();
        }
    }
}