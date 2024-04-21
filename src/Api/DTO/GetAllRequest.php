<?php

namespace App\Api\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class GetAllRequest
{
    #[Assert\PositiveOrZero]
    private int $limit = 10;

    #[Assert\PositiveOrZero]
    private int $offset = 0;

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }
}