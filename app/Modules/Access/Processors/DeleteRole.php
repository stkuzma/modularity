<?php

declare(strict_types=1);

namespace App\Modules\Access\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Exceptions\SystemRoleIsProtected;

/**
 * @implements Processor<int, null>
 */
final readonly class DeleteRole implements Processor
{
    public function __construct(
        private RoleRepository $roles,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): null
    {
        $role = $this->roles->findById($input)
            ?? throw NotFoundException::resource('roles', $input);

        if ($role->is_system) {
            throw SystemRoleIsProtected::for($role->name);
        }

        $this->roles->delete($role);

        $this->audit->record('access.role.deleted', 'role', $input, ['name' => $role->name]);

        return null;
    }
}
