<?php
declare(strict_types=1);

namespace App\Controller;

use App\Api\DTO\GetAllRequest;
use App\Api\DTO\Group\CreateGroupRequest;
use App\Api\DTO\Group\UpdateGroupRequest;
use App\Api\GroupEntityMapper;
use App\Entity\Group;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1', name: 'api_v1_')]
final class GroupController extends AbstractApiController
{

    public function __construct(
        public SerializerInterface $serializer,
        public ValidatorInterface $validator,
        public RequestStack $requestStack,
        GroupRepository $repository,
    )
    {
        $this->repository = $repository;
    }

    #[Route('/groups', name: 'get_all_groups', methods: 'GET')]
    public function getAll(GroupRepository $repository, GroupEntityMapper $mapper): Response
    {
        /** @var GetAllRequest $dto */
        $dto = $this->mapRequestToDTO(GetAllRequest::class, $error);
        if ($dto === null) {
            return new JsonResponse([
                'error' => $error
            ], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            list($error) = $errors;

            return new JsonResponse([
                'error' => sprintf('Invalid %s. %s', $error->getPropertyPath(), $error->getMessage())
            ], Response::HTTP_BAD_REQUEST);
        }

        $hasMore = false;
        $result = $repository->findBy([], null, $dto->limit + 1, $dto->offset);
        if (count($result) === $dto->limit + 1) {
            $hasMore = true;
        }

        $response = [];
        $result = array_slice($result, 0, $dto->limit);
        foreach ($result as $user) {
            $response[] = $mapper->mapToResponse($user);
        }

        return new JsonResponse([
            'response' => [
                'result' => $response,
                'hasMore' => $hasMore,
            ]
        ]);
    }

    #[Route('/groups', name: 'create_group', methods: 'POST')]
    public function create(GroupEntityMapper $mapper, EntityManagerInterface $entityManager): Response
    {
        /** @var CreateGroupRequest $dto */
        $dto = $this->mapRequestToDTO(CreateGroupRequest::class, $error);
        if ($dto === null) {
            return new JsonResponse([
                'error' => $error
            ], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            list($error) = $errors;

            return new JsonResponse([
                'error' => sprintf('Invalid %s. %s', $error->getPropertyPath(), $error->getMessage())
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->repository->findBy(['name' => $dto->name]);
        if (count($result) !== 0) {
            return new JsonResponse([
                'error' => 'Invalid name. Group with the same name already exists.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $group = new Group();
        $mapper->mapToEntity($dto, $group);
        $entityManager->persist($group);
        $entityManager->flush();

        return new JsonResponse([
            'response' => ['group' => $mapper->mapToResponse($group)]
        ], Response::HTTP_CREATED);
    }

    #[Route('/groups/{id}', name: 'update_group', methods: 'PUT')]
    public function update(GroupEntityMapper $mapper, EntityManagerInterface $entityManager): Response
    {
        /** @var UpdateGroupRequest $dto */
        $dto = $this->mapRequestToDTO(UpdateGroupRequest::class, $error);
        if ($dto === null) {
            return new JsonResponse([
                'error' => $error
            ], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            list($error) = $errors;

            return new JsonResponse([
                'error' => sprintf('Invalid %s. %s', $error->getPropertyPath(), $error->getMessage())
            ], Response::HTTP_BAD_REQUEST);
        }

        $group = $this->loadEntityById($error);
        if ($group === null) {
            return new JsonResponse([
                'error' => $error
            ], Response::HTTP_BAD_REQUEST);
        }

        $mapper->mapToEntity($dto, $group);
        $entityManager->persist($group);
        $entityManager->flush();

        return new JsonResponse([
            'response' => ['group' => $mapper->mapToResponse($group)]
        ]);
    }

    #[Route('/groups/{id}', name: 'delete_group', methods: 'DELETE')]
    public function delete(EntityManagerInterface $entityManager): Response
    {
        $group = $this->loadEntityById($error);
        if ($group === null) {
            return new JsonResponse([
                'error' => $error
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($group);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}