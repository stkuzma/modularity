<?php

declare(strict_types=1);

namespace App\Modules\Access\Data;

final readonly class RoleAssignment
{
    public function __construct(
        public int $roleId,
        public int $userId,
    ) {}
}
