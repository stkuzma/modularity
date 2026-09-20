<?php

declare(strict_types=1);

namespace App\Modules\Access\Services;

use App\Core\Access\AccessChecker;
use App\Modules\Access\Contracts\PermissionRepository;
use Illuminate\Contracts\Auth\Authenticatable;

final class DatabaseAccessChecker implements AccessChecker
{
    /** @var array<int|string, list<string>> */
    private array $memo = [];

    public function __construct(private readonly PermissionRepository $permissions) {}

    public function allows(Authenticatable $actor, string $permission): bool
    {
        return in_array($permission, $this->permissionsFor($actor), true);
    }

    public function permissionsFor(Authenticatable $actor): array
    {
        $id = $actor->getAuthIdentifier();

        if (! is_int($id) && ! is_string($id)) {
            return [];
        }

        return $this->memo[$id] ??= $this->permissions->permissionNamesForUser((int) $id);
    }

    public function forget(Authenticatable $actor): void
    {
        $id = $actor->getAuthIdentifier();

        if (is_int($id) || is_string($id)) {
            unset($this->memo[$id]);
        }
    }
}
