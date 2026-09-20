<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

interface SecondFactor
{
    public function generateSecret(): string;

    public function verify(string $secret, string $code, ?int $at = null): bool;

    public function provisioningUri(string $secret, string $account, string $issuer): string;
}
