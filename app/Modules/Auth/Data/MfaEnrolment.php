<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

final readonly class MfaEnrolment
{
    /**
     * @param  list<string>  $recoveryCodes
     */
    public function __construct(
        public string $secret,
        public string $provisioningUri,
        public array $recoveryCodes,
    ) {}
}
