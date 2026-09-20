<?php

declare(strict_types=1);

namespace App\Modules\Access\Data;

final readonly class RoleData
{
    public function __construct(
        public string $name,
        public string $label,
    ) {}
}
