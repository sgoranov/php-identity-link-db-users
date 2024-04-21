<?php

namespace App\Service;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityLoader
{
    private string $error;

    private Object $entity;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ValidatorInterface $validator,
    )
    {
    }

    public function loadEntityFromRequestById(ServiceEntityRepository $repository): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        $constraintViolationList = $this->validator->validate($request->get('id'), new Uuid());
        if ($constraintViolationList->count() !== 0) {
            $this->error = 'Invalid uuid passed.';
            return false;
        }

        $entity = $repository->find($request->get('id'));
        if (!$entity) {
            $this->error = 'Not found.';
            return false;
        }

        $this->entity = $entity;

        return true;
    }

    public function getEntity(): Object
    {
        return $this->entity;
    }

    public function respondWithError(): JsonResponse
    {
        return new JsonResponse([
            'error' => $this->error
        ], Response::HTTP_BAD_REQUEST);
    }
}