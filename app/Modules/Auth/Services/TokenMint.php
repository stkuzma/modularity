<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use Illuminate\Support\Str;

final class TokenMint
{
    public const PREFIX = 'mod_';

    public function generate(): string
    {
        return self::PREFIX.Str::random(48);
    }

    public function hash(string $plainText): string
    {
        return hash('sha256', $plainText);
    }

    public function looksLikeToken(string $candidate): bool
    {
        return str_starts_with($candidate, self::PREFIX);
    }
}
