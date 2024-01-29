<?php

namespace App\Controller;

use App\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1', name: 'api_v1_')]
final class GroupController extends AbstractController
{

    public function __construct(
        public readonly SerializerInterface $serializer,
        public readonly ValidatorInterface $validator,
        public readonly RequestStack $requestStack,
        public readonly GroupRepository $repository,
    )
    {
    }

}