<?php

declare(strict_types=1);

namespace App\Modules\Access\Exceptions;

use App\Core\Exceptions\ConflictException;

final class RoleNameTaken extends ConflictException
{
    public static function for(string $name): self
    {
        return new self(
            message: 'A role with that name already exists.',
            domainCode: 'access.role_name_taken',
            details: ['name' => $name],
        );
    }
}
