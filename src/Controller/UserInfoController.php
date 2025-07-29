<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\ConversionException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserInfoController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly GroupRepository $groupRepository,
    )
    {
    }

    #[Route('/openid/user-info', name: 'openid_user_info', methods: ['GET'])]
    #[OA\Get(
        path: '/openid/user-info',
        description: 'Returns standard OpenID user info claims for the authenticated user.',
        summary: 'Fetch info about the authenticated user',
        tags: ['User Info'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User info returned successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'preferred_username', type: 'string'),
                        new OA\Property(property: 'sub', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                        new OA\Property(
                            property: 'groups',
                            type: 'array',
                            items: new OA\Items(type: 'string')
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Missing or empty subject (sub) claim'),
            new OA\Response(response: 401, description: 'Invalid or expired access token')
        ]
    )]
    public function index(Request $request): Response
    {
        $id = $this->getUser()->getUserIdentifier();
        if (empty($id)) {
            return new JsonResponse(
                ['error' => "The 'sub' claim is required and cannot be empty."], Response::HTTP_BAD_REQUEST
            );
        }

        try {
            /** @var User $user */
            $user = $this->userRepository->getUserById($id);
        } catch (ConversionException $exception) {
            return new JsonResponse(
                ['error' => 'The access token is invalid or missing required claims.'], Response::HTTP_UNAUTHORIZED
            );
        }

        return new JsonResponse([
            'preferred_username' => $user->getLastName(),
            'sub' => $user->getId(),
            'name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'email' => $user->getEmail(),
            'groups' => $this->groupRepository->findGroupNamesByUser($user),
        ]);
    }
}