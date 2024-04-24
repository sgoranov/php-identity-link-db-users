<?php
declare(strict_types=1);

namespace App\Controller;

use App\Api\DTO\GetAllRequest;
use App\Entity\Group;
use App\Repository\GroupRepository;
use App\Service\Deserializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1', name: 'api_v1_')]
final class GroupController extends AbstractController
{

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly Deserializer $deserializer,
        private readonly GroupRepository $repository,
    )
    {
    }

    #[Route('/groups', name: 'get_all_groups', methods: 'GET')]
    public function getAll(): Response
    {
        $request = new GetAllRequest();
        if (!$this->deserializer->deserialize($request)) {
            return $this->deserializer->respondWithError();
        }

        $hasMore = false;
        $result = $this->repository->findBy([], null, $request->getLimit() + 1, $request->getOffset());
        if (count($result) === $request->getLimit() + 1) {
            $hasMore = true;
        }

        $response = [];
        $result = array_slice($result, 0, $request->getLimit());
        foreach ($result as $user) {
            $response[] = json_decode($this->serializer->serialize($user, 'json'));
        }

        return new JsonResponse([
            'response' => [
                'result' => $response,
                'hasMore' => $hasMore,
            ]
        ]);
    }

    #[Route('/groups', name: 'create_group', methods: 'POST')]
    public function create(): Response
    {
        $group = new Group();
        if (!$this->deserializer->deserialize($group, ['create'])) {
            return $this->deserializer->respondWithError();
        }

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        return new JsonResponse([
            'response' => ['group' => json_decode($this->serializer->serialize($group, 'json'))]
        ], Response::HTTP_CREATED);
    }

    #[Route('/groups/{id}', name: 'update_group', methods: 'PUT')]
    public function update(#[MapEntity(id: 'id')] Group $group): Response
    {
        if (!$this->deserializer->deserialize($group, ['update'])) {
            return $this->deserializer->respondWithError();
        }

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        return new JsonResponse([
            'response' => ['group' => json_decode($this->serializer->serialize($group, 'json'))]
        ]);
    }

    #[Route('/groups/{id}', name: 'delete_group', methods: 'DELETE')]
    public function delete(#[MapEntity(id: 'id')] Group $group): Response
    {
        $this->entityManager->remove($group);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}