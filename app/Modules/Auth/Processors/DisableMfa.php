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
final readonly class DisableMfa implements Processor
{
    public function __construct(
        private MfaRepository $mfa,
        private SecondFactor $factor,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): null
    {
        $secret = $this->mfa->findFor($input->userId) ?? throw MfaStateConflict::notEnrolled();

        $accepted = $this->factor->verify($secret->secret, $input->code)
            || $this->mfa->consumeRecoveryCode($secret, $input->code);

        if (! $accepted) {
            throw InvalidSecondFactor::make();
        }

        $this->mfa->forget($input->userId);

        $this->audit->record('auth.mfa.disabled', 'user', $input->userId);

        return null;
    }
}
