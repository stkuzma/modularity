<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Modules\Users\Enums\UserStatus;

final readonly class UserFilter
{
    public function __construct(
        public ?string $search = null,
        public ?UserStatus $status = null,
        public int $perPage = 25,
        public int $page = 1,
    ) {}
}
