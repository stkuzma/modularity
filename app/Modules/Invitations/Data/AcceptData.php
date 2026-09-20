<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Data;

final readonly class AcceptData
{
    public function __construct(
        public string $token,
        public string $name,
        public string $password,
    ) {}
}
