<?php

declare(strict_types=1);

namespace App\Modules\Access\Processors;

use App\Core\Access\PermissionCatalogue;
use App\Core\Audit\AuditTrail;
use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Data\PermissionSync;
use App\Modules\Access\Exceptions\UnknownPermission;
use App\Modules\Access\Models\Role;

/**
 * @implements Processor<PermissionSync, Role>
 */
final readonly class SyncRolePermissions implements Processor
{
    public function __construct(
        private RoleRepository $roles,
        private PermissionCatalogue $catalogue,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): Role
    {
        $role = $this->roles->findById($input->roleId)
            ?? throw NotFoundException::resource('roles', $input->roleId);

        $unknown = array_values(array_filter(
            $input->permissionNames,
            fn (string $name): bool => ! $this->catalogue->has($name),
        ));

        if ($unknown !== []) {
            throw UnknownPermission::for($unknown);
        }

        $updated = $this->roles->syncPermissions($role, $input->permissionNames);

        $this->audit->record('access.role.permissions_synced', 'role', $role->id, [
            'permissions' => $input->permissionNames,
        ]);

        return $updated;
    }
}
