<?php

declare(strict_types=1);

namespace App\Modules\Auth\Support;

use Illuminate\Http\Request;

final class SecondFactorSession
{
    public const PENDING_USER = 'auth.second_factor.pending_user';

    public const VERIFIED_AT = 'auth.second_factor.verified_at';

    public static function beginChallenge(Request $request, int $userId): void
    {
        $request->session()->put(self::PENDING_USER, $userId);
        $request->session()->forget(self::VERIFIED_AT);
    }

    public static function pendingUser(Request $request): ?int
    {
        $pending = $request->session()->get(self::PENDING_USER);

        return is_int($pending) ? $pending : null;
    }

    public static function markVerified(Request $request): void
    {
        $request->session()->forget(self::PENDING_USER);
        $request->session()->put(self::VERIFIED_AT, time());
    }

    public static function isVerified(Request $request): bool
    {
        return $request->session()->has(self::VERIFIED_AT);
    }

    public static function clear(Request $request): void
    {
        $request->session()->forget([self::PENDING_USER, self::VERIFIED_AT]);
    }
}
