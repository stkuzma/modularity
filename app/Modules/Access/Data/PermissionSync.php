<?php

declare(strict_types=1);

namespace App\Modules\Access\Data;

final readonly class PermissionSync
{
    /**
     * @param  list<string>  $permissionNames
     */
    public function __construct(
        public int $roleId,
        public array $permissionNames,
    ) {}
}
