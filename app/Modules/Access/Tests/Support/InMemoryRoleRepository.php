<?php

declare(strict_types=1);

namespace App\Modules\Access\Tests\Support;

use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Data\RoleData;
use App\Modules\Access\Models\Role;
use Illuminate\Database\Eloquent\Collection;

final class InMemoryRoleRepository implements RoleRepository
{
    /** @var array<int, Role> */
    private array $roles = [];

    /** @var array<int, list<string>> */
    private array $permissions = [];

    /** @var array<int, list<int>> */
    private array $members = [];

    private int $nextId = 1;

    public function findById(int $id): ?Role
    {
        return $this->roles[$id] ?? null;
    }

    public function findByName(string $name): ?Role
    {
        foreach ($this->roles as $role) {
            if ($role->name === $name) {
                return $role;
            }
        }

        return null;
    }

    public function existsByName(string $name, ?int $ignoreId = null): bool
    {
        foreach ($this->roles as $role) {
            if ($role->name === $name && $role->id !== $ignoreId) {
                return true;
            }
        }

        return false;
    }

    public function all(): Collection
    {
        return new Collection(array_values($this->roles));
    }

    public function create(RoleData $data): Role
    {
        return $this->store($data->name, $data->label, false);
    }

    public function createSystem(string $name, string $label): Role
    {
        return $this->store($name, $label, true);
    }

    public function update(Role $role, RoleData $data): Role
    {
        $role->setRawAttributes(array_merge($role->getAttributes(), [
            'name' => $data->name,
            'label' => $data->label,
        ]), true);

        return $this->roles[$role->id] = $role;
    }

    public function delete(Role $role): void
    {
        unset($this->roles[$role->id], $this->permissions[$role->id], $this->members[$role->id]);
    }

    public function syncPermissions(Role $role, array $permissionNames): Role
    {
        $this->permissions[$role->id] = $permissionNames;

        return $role;
    }

    public function assignToUser(Role $role, int $userId): void
    {
        $current = $this->members[$role->id] ?? [];

        if (! in_array($userId, $current, true)) {
            $current[] = $userId;
        }

        $this->members[$role->id] = $current;
    }

    public function revokeFromUser(Role $role, int $userId): void
    {
        $this->members[$role->id] = array_values(array_filter(
            $this->members[$role->id] ?? [],
            static fn (int $id): bool => $id !== $userId,
        ));
    }

    public function roleNamesForUser(int $userId): array
    {
        $names = [];

        foreach ($this->members as $roleId => $userIds) {
            if (in_array($userId, $userIds, true) && isset($this->roles[$roleId])) {
                $names[] = $this->roles[$roleId]->name;
            }
        }

        sort($names);

        return $names;
    }

    /**
     * @return list<string>
     */
    public function permissionsOf(int $roleId): array
    {
        return $this->permissions[$roleId] ?? [];
    }

    private function store(string $name, string $label, bool $isSystem): Role
    {
        $role = new Role;

        $role->setRawAttributes([
            'id' => $this->nextId++,
            'name' => $name,
            'label' => $label,
            'is_system' => $isSystem,
        ], true);

        $role->exists = true;

        return $this->roles[$role->id] = $role;
    }
}
