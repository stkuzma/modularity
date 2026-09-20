<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Modules\Users\Enums\UserStatus;

final readonly class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserStatus $status = UserStatus::Active,
    ) {}
}
