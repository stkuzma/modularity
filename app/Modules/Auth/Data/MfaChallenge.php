<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

final readonly class MfaChallenge
{
    public function __construct(
        public int $userId,
        public string $code,
    ) {}
}
