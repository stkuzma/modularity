<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Exceptions\UnauthorizedException;
use Illuminate\Contracts\Auth\Authenticatable;

final class ActorId
{
    public static function required(?Authenticatable $actor): int
    {
        $id = self::optional($actor);

        if ($id === null) {
            throw new UnauthorizedException;
        }

        return $id;
    }

    public static function optional(?Authenticatable $actor): ?int
    {
        $id = $actor?->getAuthIdentifier();

        if (is_int($id)) {
            return $id;
        }

        return is_string($id) && ctype_digit($id) ? (int) $id : null;
    }
}
