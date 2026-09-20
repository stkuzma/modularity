<?php

declare(strict_types=1);

namespace App\Modules\Access\Exceptions;

use App\Core\Exceptions\ConflictException;

final class SystemRoleIsProtected extends ConflictException
{
    public static function for(string $name): self
    {
        return new self(
            message: 'System roles cannot be renamed or deleted.',
            domainCode: 'access.system_role_protected',
            details: ['name' => $name],
        );
    }
}
