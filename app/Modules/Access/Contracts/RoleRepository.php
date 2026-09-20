<?php

declare(strict_types=1);

namespace App\Modules\Access\Contracts;

use App\Modules\Access\Data\RoleData;
use App\Modules\Access\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepository
{
    public function findById(int $id): ?Role;

    public function findByName(string $name): ?Role;

    public function existsByName(string $name, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, Role>
     */
    public function all(): Collection;

    public function create(RoleData $data): Role;

    public function update(Role $role, RoleData $data): Role;

    public function delete(Role $role): void;

    /**
     * @param  list<string>  $permissionNames
     */
    public function syncPermissions(Role $role, array $permissionNames): Role;

    public function assignToUser(Role $role, int $userId): void;

    public function revokeFromUser(Role $role, int $userId): void;

    /**
     * @return list<string>
     */
    public function roleNamesForUser(int $userId): array;
}
