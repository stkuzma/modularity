<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Pipeline\Processor;
use App\Modules\Auth\Contracts\MfaRepository;
use App\Modules\Auth\Contracts\SecondFactor;
use App\Modules\Auth\Data\MfaChallenge;
use App\Modules\Auth\Exceptions\InvalidSecondFactor;
use App\Modules\Auth\Exceptions\MfaStateConflict;

/**
 * @implements Processor<MfaChallenge, null>
 */
final readonly class VerifySecondFactor implements Processor
{
    public function __construct(
        private MfaRepository $mfa,
        private SecondFactor $factor,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): null
    {
        $secret = $this->mfa->findFor($input->userId) ?? throw MfaStateConflict::notEnrolled();

        if (! $secret->isConfirmed()) {
            throw MfaStateConflict::notEnrolled();
        }

        if ($this->factor->verify($secret->secret, $input->code)) {
            $this->audit->record('auth.mfa.verified', 'user', $input->userId, ['factor' => 'totp']);

            return null;
        }

        if ($this->mfa->consumeRecoveryCode($secret, $input->code)) {
            $this->audit->record('auth.mfa.verified', 'user', $input->userId, ['factor' => 'recovery_code']);

            return null;
        }

        $this->audit->record('auth.mfa.verification_failed', 'user', $input->userId);

        throw InvalidSecondFactor::make();
    }
}
