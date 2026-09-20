<?php

declare(strict_types=1);

namespace App\Core\Access;

use Illuminate\Contracts\Auth\Authenticatable;

final class DeniesEverything implements AccessChecker
{
    public function allows(Authenticatable $actor, string $permission): bool
    {
        return false;
    }

    public function permissionsFor(Authenticatable $actor): array
    {
        return [];
    }
}
