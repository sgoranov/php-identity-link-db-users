<?php

namespace App\Api\DTO\Group;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateGroupRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    #[Assert\Regex('/^([\.\w0-9_ :-])+$/u')]
    public ?string $name;
}