<?php

declare(strict_types=1);

namespace App\Modules\Access\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Data\RoleData;
use App\Modules\Access\Exceptions\RoleNameTaken;
use App\Modules\Access\Exceptions\SystemRoleIsProtected;
use App\Modules\Access\Models\Role;

/**
 * @phpstan-type UpdateRoleInput array{id: int, data: RoleData}
 *
 * @implements Processor<UpdateRoleInput, Role>
 */
final readonly class UpdateRole implements Processor
{
    public function __construct(
        private RoleRepository $roles,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): Role
    {
        $role = $this->roles->findById($input['id'])
            ?? throw NotFoundException::resource('roles', $input['id']);

        if ($role->is_system) {
            throw SystemRoleIsProtected::for($role->name);
        }

        $data = $input['data'];

        if ($this->roles->existsByName($data->name, $role->id)) {
            throw RoleNameTaken::for($data->name);
        }

        $before = $role->name;
        $updated = $this->roles->update($role, $data);

        $this->audit->record('access.role.updated', 'role', $updated->id, [
            'from' => $before,
            'to' => $updated->name,
        ]);

        return $updated;
    }
}
