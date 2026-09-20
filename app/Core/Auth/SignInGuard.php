<?php

declare(strict_types=1);

namespace App\Core\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

/** May this account sign in at all, credentials aside. */
interface SignInGuard
{
    public function maySignIn(Authenticatable $user): bool;

    public function reason(Authenticatable $user): ?string;
}
