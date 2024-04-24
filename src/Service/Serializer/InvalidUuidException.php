<?php
declare(strict_types=1);

namespace App\Service\Serializer;

use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class InvalidUuidException extends UnexpectedValueException
{

    private string $uuid;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }
}