<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use Illuminate\Contracts\Auth\Authenticatable;

final readonly class SignInResult
{
    public function __construct(
        public Authenticatable $user,
        public bool $requiresSecondFactor,
    ) {}
}
