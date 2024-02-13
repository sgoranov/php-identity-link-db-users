<?php

namespace App\Api;

use App\Api\DTO\Group\GroupResponse;
use App\Entity\Group;

class GroupEntityMapper
{
    public function mapToResponse(Group $group): GroupResponse
    {
        $response = new GroupResponse();
        $response->id = $group->getId();
        $response->name = $group->getName();

        return $response;
    }

    public function mapToEntity($dto, Group $group): void
    {
        $properties = [
            'name',
        ];

        foreach ($properties as $property) {
            try {
                $group->{'set' . ucfirst($property)}($dto->$property);
            } catch (\Error $e) {
                if (!str_ends_with($e->getMessage(), 'must not be accessed before initialization')) {
                    throw $e;
                }
            }
        }
    }
}