<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Core\Auth\ActorId;
use App\Modules\Auth\Http\Requests\SignInRequest;
use App\Modules\Auth\Processors\SignIn;
use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;

final class SignInFormController
{
    public function __construct(
        private readonly SignIn $processor,
        private readonly StatefulGuard $guard,
    ) {}

    public function __invoke(SignInRequest $request): RedirectResponse
    {
        $credentials = $request->toCredentials();
        $result = $this->processor->process($credentials);

        if ($result->requiresSecondFactor) {
            SecondFactorSession::beginChallenge($request, ActorId::required($result->user));

            return redirect()->route('second-factor.show');
        }

        $this->guard->login($result->user, $credentials->remember);
        $request->session()->regenerate();
        SecondFactorSession::markVerified($request);

        return redirect()->intended(route('dashboard'));
    }
}
