<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PublicController extends AbstractController
{
    #[Route('/api/v1/users', name: 'api_users', methods: 'GET')]
    public function index(UserRepository $userRepository): JsonResponse
    {
        return $this->json([
            'response' => $userRepository->findAll(),
        ]);
    }
}
