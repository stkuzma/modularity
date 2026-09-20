<?php

declare(strict_types=1);

namespace App\Modules\Access\Repositories;

use App\Modules\Access\Contracts\PermissionRepository;
use App\Modules\Access\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

final class EloquentPermissionRepository implements PermissionRepository
{
    public function all(): Collection
    {
        return Permission::query()->orderBy('module')->orderBy('name')->get();
    }

    public function names(): array
    {
        /** @var list<string> $names */
        $names = Permission::query()->orderBy('name')->pluck('name')->all();

        return $names;
    }

    public function syncWithCatalogue(array $declared, bool $prune = false): array
    {
        $existing = Permission::query()->get()->keyBy('name');
        $created = 0;
        $updated = 0;

        foreach ($declared as $name => $meta) {
            $permission = $existing->get($name);

            if ($permission === null) {
                Permission::query()->create([
                    'name' => $name,
                    'label' => $meta['label'],
                    'module' => $meta['module'],
                ]);
                $created++;

                continue;
            }

            if ($permission->label !== $meta['label'] || $permission->module !== $meta['module']) {
                $permission->fill(['label' => $meta['label'], 'module' => $meta['module']])->save();
                $updated++;
            }
        }

        if (! $prune) {
            return ['created' => $created, 'updated' => $updated, 'removed' => 0];
        }

        // Undeclared permissions are dead weight; pivot rows cascade.
        $removed = Permission::query()
            ->whereNotIn('name', array_keys($declared))
            ->get()
            ->each(fn (Permission $permission): mixed => $permission->delete())
            ->count();

        return ['created' => $created, 'updated' => $updated, 'removed' => $removed];
    }

    public function permissionNamesForUser(int $userId): array
    {
        /** @var list<string> $names */
        $names = Permission::query()
            ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->join('role_user', 'permission_role.role_id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $userId)
            ->distinct()
            ->orderBy('permissions.name')
            ->pluck('permissions.name')
            ->all();

        return $names;
    }
}
