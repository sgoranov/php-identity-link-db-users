<?php

namespace App\Controller;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractApiController extends AbstractController
{
    public SerializerInterface $serializer;
    public ValidatorInterface $validator;
    public RequestStack $requestStack;
    public ServiceEntityRepository $repository;

    protected function loadEntityById(&$error): ?object
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var ConstraintViolationList $errors */
        $errors = $this->validator->validate($request->get('id'), new Uuid());
        if ($errors->count() !== 0) {
            $error = 'Invalid uuid passed.';
            return null;
        }

        $entity = $this->repository->find($request->get('id'));
        if (!$entity) {
            $error = 'Not found.';
            return null;
        }

        return $entity;
    }

    protected function mapRequestToDTO(string $dtoClassName, &$error): ?object
    {
        try {

            return $this->serializer->deserialize($this->requestStack->getCurrentRequest()->getContent(), $dtoClassName, 'json', [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ]);

        } catch (ExtraAttributesException $e) {

            $error = $e->getMessage();

        } catch (NotNormalizableValueException $e) {

            $error = sprintf("The %s property must be of type %s, but %s was provided.",
                $e->getPath(), implode('|', $e->getExpectedTypes()), $e->getCurrentType());

        } catch (\Exception $e) {

            // TODO: log the error
            $error = 'Error while deserializing the data.';
        }

        return null;
    }
}