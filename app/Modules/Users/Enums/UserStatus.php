<?php

declare(strict_types=1);

namespace App\Modules\Users\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
        };
    }

    public function canSignIn(): bool
    {
        return $this === self::Active;
    }
}
