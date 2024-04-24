<?php
declare(strict_types=1);

namespace App\Service\Serializer;

use App\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DoctrineCollectionNormalizer implements NormalizerInterface, DenormalizerInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): ArrayCollection
    {
        $collection = new ArrayCollection();
        foreach ($data as $uuid) {
            $entity = $this->entityManager->find(rtrim($type, '[]'), $uuid);
            if (!$entity) {
                $exception = new InvalidUuidException();
                $exception->setUuid($uuid);

                throw $exception;
            }

            $collection->add($entity);
        }

        return $collection;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if ($type === Group::class . '[]' && is_array($data)) {

            return true;
        }

        return false;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        $data = [];
        foreach ($object as $entity) {
            $data[] = $entity->getId();
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        if ($data instanceof PersistentCollection) {
            return true;
        }

        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Group::class . '[]' => true,
            PersistentCollection::class => true,
        ];
    }
}