<?php
declare(strict_types=1);

namespace App\Api\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class AuthUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 200)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 200)]
    public string $password;

    #[Assert\Choice(['client_credentials', 'password', 'authorization_code', 'refresh_token', 'implicit'])]
    public ?string $grantType;
}
