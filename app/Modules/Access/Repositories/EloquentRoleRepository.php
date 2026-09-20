<?php

declare(strict_types=1);

namespace App\Modules\Access\Repositories;

use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Data\RoleData;
use App\Modules\Access\Models\Permission;
use App\Modules\Access\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class EloquentRoleRepository implements RoleRepository
{
    public function findById(int $id): ?Role
    {
        return Role::query()->with('permissions')->find($id);
    }

    public function findByName(string $name): ?Role
    {
        return Role::query()->with('permissions')->where('name', $name)->first();
    }

    public function existsByName(string $name, ?int $ignoreId = null): bool
    {
        return Role::query()
            ->where('name', $name)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function all(): Collection
    {
        return Role::query()->with('permissions')->orderBy('name')->get();
    }

    public function create(RoleData $data): Role
    {
        $role = new Role;

        $role->name = $data->name;
        $role->label = $data->label;
        $role->is_system = false;
        $role->save();

        return $role->load('permissions');
    }

    public function update(Role $role, RoleData $data): Role
    {
        $role->name = $data->name;
        $role->label = $data->label;
        $role->save();

        return $role->load('permissions');
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function syncPermissions(Role $role, array $permissionNames): Role
    {
        $ids = Permission::query()
            ->whereIn('name', $permissionNames)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($ids);

        return $role->load('permissions');
    }

    public function assignToUser(Role $role, int $userId): void
    {
        $role->newQuery()->getConnection()->table('role_user')->updateOrInsert([
            'role_id' => $role->id,
            'user_id' => $userId,
        ]);
    }

    public function revokeFromUser(Role $role, int $userId): void
    {
        $role->newQuery()->getConnection()->table('role_user')
            ->where('role_id', $role->id)
            ->where('user_id', $userId)
            ->delete();
    }

    public function roleNamesForUser(int $userId): array
    {
        /** @var list<string> $names */
        $names = Role::query()
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $userId)
            ->orderBy('roles.name')
            ->pluck('roles.name')
            ->all();

        return $names;
    }
}
