<?php

declare(strict_types=1);

namespace App\Core\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

final class AllowsAnyAccount implements SignInGuard
{
    public function maySignIn(Authenticatable $user): bool
    {
        return true;
    }

    public function reason(Authenticatable $user): ?string
    {
        return null;
    }
}
