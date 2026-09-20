<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

final readonly class TokenRequest
{
    public function __construct(
        public int $userId,
        public string $name,
        public ?int $expiresInDays = null,
    ) {}
}
