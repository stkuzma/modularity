<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Auth\ActorId;
use App\Core\Auth\SignInGuard;
use App\Core\Pipeline\Processor;
use App\Modules\Auth\Contracts\MfaRepository;
use App\Modules\Auth\Data\Credentials;
use App\Modules\Auth\Data\SignInResult;
use App\Modules\Auth\Exceptions\InvalidCredentials;
use App\Modules\Auth\Exceptions\SignInNotPermitted;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * @implements Processor<Credentials, SignInResult>
 */
final readonly class SignIn implements Processor
{
    public function __construct(
        private UserProvider $users,
        private SignInGuard $eligibility,
        private MfaRepository $mfa,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): SignInResult
    {
        $user = $this->users->retrieveByCredentials($input->forProvider());

        if ($user === null || ! $this->users->validateCredentials($user, $input->forProvider())) {
            $this->audit->record('auth.sign_in_failed', null, null, ['email' => $input->email]);

            throw InvalidCredentials::make();
        }

        if (! $this->eligibility->maySignIn($user)) {
            $reason = $this->eligibility->reason($user) ?? 'not_permitted';

            $this->audit->record('auth.sign_in_blocked', 'user', $this->idOf($user), ['reason' => $reason]);

            throw SignInNotPermitted::because($reason);
        }

        $secret = $this->mfa->findFor($this->idOf($user));
        $requiresSecondFactor = $secret !== null && $secret->isConfirmed();

        $this->audit->record('auth.sign_in', 'user', $this->idOf($user), [
            'second_factor_required' => $requiresSecondFactor,
        ]);

        return new SignInResult($user, $requiresSecondFactor);
    }

    private function idOf(Authenticatable $user): int
    {
        return ActorId::required($user);
    }
}
