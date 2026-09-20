<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

use App\Modules\Auth\Models\MfaSecret;

interface MfaRepository
{
    public function findFor(int $userId): ?MfaSecret;

    /**
     * @param  list<string>  $recoveryCodes
     */
    public function store(int $userId, string $secret, array $recoveryCodes): MfaSecret;

    public function confirm(MfaSecret $secret): MfaSecret;

    /**
     * @return bool whether the code existed and was consumed
     */
    public function consumeRecoveryCode(MfaSecret $secret, string $code): bool;

    public function forget(int $userId): void;
}
