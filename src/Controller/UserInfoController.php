<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\ConversionException;
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