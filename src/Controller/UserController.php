<?php
declare(strict_types=1);

namespace App\Controller;

use App\Api\DTO\User\AuthUserRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use sgoranov\PHPIdentityLinkShared\Serializer\Deserializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1', name: 'api_v1_')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly Deserializer $deserializer,
        private readonly UserRepository $repository,
    )
    {
    }

    #[Route('/user/{id}', name: 'fetch_user', methods: 'GET')]
    public function fetch(#[MapEntity(id: 'id')] User $user): Response
    {
        return new JsonResponse([
            'response' => ['user' => json_decode($this->serializer->serialize($user, 'json'))]
        ]);
    }

    #[Route('/user', name: 'create_user', methods: 'POST')]
    public function create(): Response
    {
        $user = new User();
        if (!$this->deserializer->deserialize($user, ['create'])) {
            return $this->deserializer->respondWithError();
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('response_without_password')
            ->toArray();

        return new JsonResponse([
            'response' => ['user' => json_decode($this->serializer->serialize($user, 'json', $context))]
        ], Response::HTTP_CREATED);
    }

    #[Route('/user/{id}', name: 'update_user', methods: 'PUT')]
    public function update(#[MapEntity(id: 'id')] User $user): Response
    {
        if (!$this->deserializer->deserialize($user, ['update'])) {
            return $this->deserializer->respondWithError();
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'response' => ['user' => json_decode($this->serializer->serialize($user, 'json'))]
        ]);
    }

    #[Route('/user/{id}', name: 'delete_user', methods: 'DELETE')]
    public function delete(#[MapEntity(id: 'id')] User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/auth', name: 'auth', methods: 'POST')]
    public function auth(): Response
    {
        $authRequest = new AuthUserRequest();
        if (!$this->deserializer->deserialize($authRequest)) {
            return $this->deserializer->respondWithError();
        }

        $user = $this->repository->getUserByUsernameAndPassword(
            $authRequest->getUsername(), $authRequest->getPassword());
        if ($user === null) {
            return new JsonResponse([
                'error' => 'User not found.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!empty($authRequest->grantType) && !in_array($authRequest->grantType, $user->getGrantTypes())) {
            return new JsonResponse([
                'error' => 'Invalid grant type.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'response' => ['user' => json_decode($this->serializer->serialize($user, 'json'))]
        ]);
    }
}
