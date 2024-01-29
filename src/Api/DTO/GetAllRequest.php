<?php

namespace App\Api\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class GetAllRequest
{
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public int $limit = 10;

    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public int $offset = 0;
}