<?php

declare(strict_types=1);

namespace App\Modules\Access\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Data\RoleAssignment;

/**
 * @implements Processor<RoleAssignment, null>
 */
final readonly class AssignRole implements Processor
{
    public function __construct(
        private RoleRepository $roles,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): null
    {
        $role = $this->roles->findById($input->roleId)
            ?? throw NotFoundException::resource('roles', $input->roleId);

        $this->roles->assignToUser($role, $input->userId);

        $this->audit->record('access.role.assigned', 'user', $input->userId, [
            'role' => $role->name,
        ]);

        return null;
    }
}
