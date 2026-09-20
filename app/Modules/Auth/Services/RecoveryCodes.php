<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use Illuminate\Support\Str;

final class RecoveryCodes
{
    private const COUNT = 8;

    /**
     * @return list<string>
     */
    public function generate(): array
    {
        $codes = [];

        for ($i = 0; $i < self::COUNT; $i++) {
            $codes[] = strtolower(Str::random(5).'-'.Str::random(5));
        }

        return $codes;
    }
}
