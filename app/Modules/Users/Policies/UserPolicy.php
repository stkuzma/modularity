<?php

declare(strict_types=1);

namespace App\Modules\Users\Policies;

use App\Modules\Users\Models\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->status->canSignIn();
    }

    public function view(User $actor, User $subject): bool
    {
        return $actor->is($subject);
    }

    public function update(User $actor, User $subject): bool
    {
        return $actor->is($subject);
    }

    public function delete(User $actor, User $subject): bool
    {
        return false;
    }
}
