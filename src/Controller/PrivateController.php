<?php
declare(strict_types=1);

namespace App\Controller;

use App\Api\Private\DTO\UserRequest;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PrivateController extends AbstractController
{
    #[Route('/api/private/v1/user', name: 'api_private_fetch_user', methods: 'GET')]
    public function getUserEntityByUserCredentials(
        Request $request,
        ValidatorInterface $validator,
        UserRepository $repository,
        SerializerInterface $serializer,
    ): Response
    {
        try {
            $dto = $serializer->deserialize($request->getContent(), UserRequest::class, 'json');
        } catch (\Exception $e) {
            return new JsonResponse(['response' => [$this->createError('Unable to deserialize the request')]], 400);
        }

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['response' => $this->constraintViolationsToArray($errors)], 400);
        }

        $user = $repository->getUser($dto->username, $dto->password);
        if ($user == null) {
            return new JsonResponse(['response' => [$this->createError('User not found')]], 404);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
        ]);
    }

    private function constraintViolationsToArray(ConstraintViolationList $errors): array
    {
        $result = [];

        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $result[] = $this->createError($error->getMessage(), $error->getPropertyPath());
        }

        return $result;
    }

    private function createError(string $message, ?string $field = null): string
    {
        if ($field) {
            return sprintf('ERROR (%s): %s', $field, $message);
        }

        return sprintf('ERROR: %s', $message);
    }
}
