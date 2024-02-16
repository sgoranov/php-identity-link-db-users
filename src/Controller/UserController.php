<?php
declare(strict_types=1);

namespace App\Controller;

use App\Api\DTO\GetAllRequest;
use App\Api\DTO\User\AuthUserRequest;
use App\Api\DTO\User\CreateUserRequest;
use App\Api\DTO\User\UpdateUserRequest;
use App\Api\UserEntityMapper;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1', name: 'api_v1_')]
final class UserController extends AbstractApiController
{
    public function __construct(
        public SerializerInterface $serializer,
        public ValidatorInterface $validator,
        public RequestStack $requestStack,
        UserRepository $repository,
    )
    {
        $this->repository = $repository;
    }

    #[Route('/users', name: 'get_all_users', methods: 'GET')]
    public function getAll(UserRepository $repository, UserEntityMapper $mapper): Response
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

    #[Route('/users', name: 'create_user', methods: 'POST')]
    public function create(UserEntityMapper $mapper, EntityManagerInterface $entityManager): Response
    {
        $dto = $this->mapRequestToDTO(CreateUserRequest::class, $error);
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

        $result = $this->repository->findBy(['username' => $dto->username]);
        if (count($result) !== 0) {
            return new JsonResponse([
                'error' => 'Invalid username. User with the same username already exists.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $mapper->mapToEntity($dto, $user);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'response' => ['user' => $mapper->mapToResponse($user)]
        ], Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'update_user', methods: 'PUT')]
    public function update(UserEntityMapper $mapper, EntityManagerInterface $entityManager): Response
    {
        /** @var UpdateUserRequest $dto */
        $dto = $this->mapRequestToDTO(UpdateUserRequest::class, $error);
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

        $user = $this->loadEntityById($error);
        if ($user === null) {
            return new JsonResponse([
                'error' => $error
            ], Response::HTTP_BAD_REQUEST);
        }

        $mapper->mapToEntity($dto, $user);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'response' => ['user' => $mapper->mapToResponse($user)]
        ]);
    }

    #[Route('/users/{id}', name: 'delete_user', methods: 'DELETE')]
    public function delete(EntityManagerInterface $entityManager): Response
    {
        $user = $this->loadEntityById($error);
        if ($user === null) {
            return new JsonResponse([
                'error' => $error
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/auth', name: 'auth', methods: 'POST')]
    public function auth(UserRepository $repository, UserEntityMapper $mapper): Response
    {
        /** @var AuthUserRequest $dto */
        $dto = $this->mapRequestToDTO(AuthUserRequest::class, $error);
        if ($dto === null) {
            return new JsonResponse([
                'error' => $error
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $repository->getUser($dto->username, $dto->password);
        if ($user === null) {
            return new JsonResponse([
                'error' => 'User not found.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'response' => ['user' => $mapper->mapToResponse($user)]
        ]);
    }
}
