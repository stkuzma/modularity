<?php

declare(strict_types=1);

namespace App\Modules\Access\Exceptions;

use App\Core\Exceptions\ConflictException;

final class UnknownPermission extends ConflictException
{
    /**
     * @param  list<string>  $names
     */
    public static function for(array $names): self
    {
        return new self(
            message: 'Those permissions are not declared by any module.',
            domainCode: 'access.unknown_permission',
            details: ['permissions' => $names],
        );
    }
}
