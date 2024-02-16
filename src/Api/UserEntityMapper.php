<?php

namespace App\Api;

use App\Api\DTO\User\UserResponse;
use App\Entity\User;
use App\Service\PasswordHashGenerator;

class UserEntityMapper
{
    public function mapToResponse(User $user): UserResponse
    {
        $response = new UserResponse();
        $response->id = $user->getId();
        $response->username = $user->getUsername();
        $response->firstName = $user->getFirstName();
        $response->lastName = $user->getLastName();
        $response->email = $user->getEmail();

        return $response;
    }

    public function mapToEntity($dto, User $user): void
    {
        $properties = [
            'username',
            'password',
            'firstName',
            'lastName',
            'email',
            'groups'
        ];

        foreach ($properties as $property) {
            try {
                if ($property === 'password') {
                    $user->setPassword(PasswordHashGenerator::create($dto->password));
                } else {
                    // skip all properties which are not initialized
                    if (property_exists($dto, $property)) {
                        $user->{'set' . ucfirst($property)}($dto->$property);
                    }
                }
            } catch (\Error $e) {
                if (!str_ends_with($e->getMessage(), 'must not be accessed before initialization')) {
                    throw $e;
                }
            }
        }
    }
}