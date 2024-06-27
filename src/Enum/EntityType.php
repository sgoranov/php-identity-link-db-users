<?php
declare(strict_types=1);

namespace App\Enum;

use App\Entity\Group;
use App\Entity\User;

enum EntityType: string
{
    case USER = 'User';
    case GROUP = 'Group';

    public function entity(): string {
        return EntityType::getEntity($this);
    }

    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    public static function getEntity(self $value): string {
        return match ($value) {
            EntityType::USER => User::class,
            EntityType::GROUP => Group::class,
        };
    }
}
