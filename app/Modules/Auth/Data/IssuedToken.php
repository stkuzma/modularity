<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use App\Modules\Auth\Models\AuthToken;

final readonly class IssuedToken
{
    public function __construct(
        public AuthToken $token,
        public string $plainText,
    ) {}
}
