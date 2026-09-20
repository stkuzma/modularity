<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Contracts\MfaRepository;
use App\Modules\Auth\Models\MfaSecret;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

final class EloquentMfaRepository implements MfaRepository
{
    public function findFor(int $userId): ?MfaSecret
    {
        return MfaSecret::query()->find($userId);
    }

    public function store(int $userId, string $secret, array $recoveryCodes): MfaSecret
    {
        $record = $this->findFor($userId) ?? new MfaSecret(['user_id' => $userId]);

        $record->user_id = $userId;
        $record->secret = $secret;
        // Recovery codes are credentials, hashed like passwords.
        $record->recovery_codes = array_map(static fn (string $code): string => Hash::make($code), $recoveryCodes);
        $record->confirmed_at = null;
        $record->save();

        return $record;
    }

    public function confirm(MfaSecret $secret): MfaSecret
    {
        $secret->confirmed_at = Carbon::now();
        $secret->save();

        return $secret;
    }

    public function consumeRecoveryCode(MfaSecret $secret, string $code): bool
    {
        $remaining = [];
        $consumed = false;

        foreach ($secret->recovery_codes as $hashed) {
            if (! $consumed && Hash::check($code, $hashed)) {
                $consumed = true;

                continue;
            }

            $remaining[] = $hashed;
        }

        if ($consumed) {
            $secret->recovery_codes = $remaining;
            $secret->save();
        }

        return $consumed;
    }

    public function forget(int $userId): void
    {
        MfaSecret::query()->whereKey($userId)->delete();
    }
}
