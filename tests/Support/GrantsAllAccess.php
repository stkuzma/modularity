<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Core\Access\AccessChecker;
use Illuminate\Contracts\Auth\Authenticatable;

final class GrantsAllAccess implements AccessChecker
{
    /**
     * @param  list<string>  $permissions  an empty list grants everything
     */
    public function __construct(private readonly array $permissions = []) {}

    public function allows(Authenticatable $actor, string $permission): bool
    {
        return $this->permissions === [] || in_array($permission, $this->permissions, true);
    }

    public function permissionsFor(Authenticatable $actor): array
    {
        return $this->permissions;
    }
}
