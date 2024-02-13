<?php

namespace App\Service\Validator;

use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class GroupIdValidator extends ConstraintValidator {

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        $groupEntity = $this->entityManager->getRepository(Group::class)->find($value);
        if (!$groupEntity) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}