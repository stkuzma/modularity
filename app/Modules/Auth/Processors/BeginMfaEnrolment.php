<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Pipeline\Processor;
use App\Modules\Auth\Contracts\MfaRepository;
use App\Modules\Auth\Contracts\SecondFactor;
use App\Modules\Auth\Data\MfaEnrolment;
use App\Modules\Auth\Exceptions\MfaStateConflict;
use App\Modules\Auth\Services\RecoveryCodes;

/**
 * @phpstan-type EnrolInput array{userId: int, account: string, issuer: string}
 *
 * @implements Processor<EnrolInput, MfaEnrolment>
 */
final readonly class BeginMfaEnrolment implements Processor
{
    public function __construct(
        private MfaRepository $mfa,
        private SecondFactor $factor,
        private RecoveryCodes $recovery,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): MfaEnrolment
    {
        $existing = $this->mfa->findFor($input['userId']);

        if ($existing?->isConfirmed() === true) {
            throw MfaStateConflict::alreadyConfirmed();
        }

        $secret = $this->factor->generateSecret();
        $codes = $this->recovery->generate();

        // Unconfirmed: an abandoned setup must not lock the account out.
        $this->mfa->store($input['userId'], $secret, $codes);

        $this->audit->record('auth.mfa.enrolment_started', 'user', $input['userId']);

        return new MfaEnrolment(
            secret: $secret,
            provisioningUri: $this->factor->provisioningUri($secret, $input['account'], $input['issuer']),
            recoveryCodes: $codes,
        );
    }
}
