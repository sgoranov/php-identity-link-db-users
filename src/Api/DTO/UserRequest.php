<?php
declare(strict_types=1);

namespace App\Api\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 200)]
        public readonly string $username,

        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 200)]
        public readonly string $password,

        #[Assert\Choice(['client_credentials', 'password', 'authorization_code', 'refresh_token', 'implicit'])]
        public readonly ?string $grantType,
    ) {
    }
}
