<?php

declare(strict_types=1);

namespace App\Modules\Access\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Pipeline\Processor;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Data\RoleData;
use App\Modules\Access\Exceptions\RoleNameTaken;
use App\Modules\Access\Models\Role;

/**
 * @implements Processor<RoleData, Role>
 */
final readonly class CreateRole implements Processor
{
    public function __construct(
        private RoleRepository $roles,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): Role
    {
        if ($this->roles->existsByName($input->name)) {
            throw RoleNameTaken::for($input->name);
        }

        $role = $this->roles->create($input);

        $this->audit->record('access.role.created', 'role', $role->id, ['name' => $role->name]);

        return $role;
    }
}
