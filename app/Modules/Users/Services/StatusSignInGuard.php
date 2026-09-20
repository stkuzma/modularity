<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Core\Auth\SignInGuard;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

final class StatusSignInGuard implements SignInGuard
{
    public function maySignIn(Authenticatable $user): bool
    {
        return ! $user instanceof User || $user->status->canSignIn();
    }

    public function reason(Authenticatable $user): ?string
    {
        return $this->maySignIn($user) ? null : 'account_suspended';
    }
}
