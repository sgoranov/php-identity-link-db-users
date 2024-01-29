<?php

namespace App\Api\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserRequest
{
    #[Assert\Length(min: 1, max: 50)]
    public ?string $password;

    #[Assert\Length(min: 1, max: 100)]
    public ?string $firstName;

    #[Assert\Length(min: 1, max: 100)]
    public ?string $lastName;

    #[Assert\Email]
    #[Assert\Length(min: 1, max: 100)]
    public ?string $email;
}