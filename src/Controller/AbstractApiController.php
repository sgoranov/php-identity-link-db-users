<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractApiController extends AbstractController
{
    public SerializerInterface $serializer;
    public ValidatorInterface $validator;
    public RequestStack $requestStack;
    public ServiceEntityRepository $repository;

    protected function loadEntityById(&$error): ?User
    {
        $request = $this->requestStack->getCurrentRequest();

        $errors = $this->validator->validate($request->get('id'), new Uuid());
        if (count($errors) !== 0) {
            $error = 'Invalid uuid passed.';
        }

        $user = $this->repository->getUserById($request->get('id'));
        if ($user === null) {
            $error = 'Not found.';
        }

        return $user;
    }

    protected function mapRequestToDTO(string $dtoClassName, &$error): ?object
    {
        try {

            return $this->serializer->deserialize($this->requestStack->getCurrentRequest()->getContent(), $dtoClassName, 'json', [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ]);

        } catch (ExtraAttributesException $e) {

            $error = $e->getMessage();

        } catch (\Exception $e) {

            // TODO: log the error
            $error = 'Error while deserializing the data.';
        }

        return null;
    }
}