<?php
declare(strict_types=1);

namespace App\Controller\Api\Internal;

use App\Api\DTO\UserRequest;
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

class GetUserEntityByUserCredentialsController extends AbstractController
{
    public function __construct(
        public readonly SerializerInterface $serializer,
        public readonly ValidatorInterface $validator,
    )
    {
    }

    #[Route('/internal/v1/user', name: 'api_internal_get_user_entity_by_user_credentials', methods: 'GET')]
    public function getUserEntityByUserCredentials(
        Request $request,
        UserRepository $repository,
    ): Response
    {
        $dto = $this->getDTOFromRequest($request, UserRequest::class);

        $user = $repository->getUser($dto->username, $dto->password);
        if ($user == null) {
            return new JsonResponse(['response' => [$this->createError('User not found')]], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
        ]);
    }

    private function getDTOFromRequest(Request $request, string $dtoClass): object
    {
        try {
            $dto = $this->serializer->deserialize($request->getContent(), $dtoClass, 'json');
        } catch (\Exception $e) {
            return new JsonResponse(['response' => [$this->createError('Unable to deserialize the request')]], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['response' => $this->constraintViolationsToArray($errors)], Response::HTTP_BAD_REQUEST);
        }

        return $dto;
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
