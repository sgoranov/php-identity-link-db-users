<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

final class User implements JWTUserInterface
{
    const ROLE_ADMIN_MAPPING = 'administrator';

    public function __construct(
        private readonly string $username,
        private readonly array  $roles,
    )
    {
    }

    public static function createFromPayload($username, array $payload): JWTUserInterface|User
    {
        $roles = [];
        if (isset($payload['groups']) && is_array($payload['groups']) &&
            in_array(self::ROLE_ADMIN_MAPPING, $payload['groups'], true)) {

            $roles[] = 'ROLE_ADMIN';
        }

        return new self(
            $username,
            $roles,
        );
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {
        // there is no sensitive data to remove
        // from the User object
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}