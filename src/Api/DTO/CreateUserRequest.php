<?php

namespace App\Api\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    public string $firstName;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    public string $lastName;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(min: 1, max: 100)]
    public string $email;

    public string $groups;
    
}