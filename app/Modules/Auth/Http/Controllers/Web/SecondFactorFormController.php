<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Modules\Auth\Data\MfaChallenge;
use App\Modules\Auth\Http\Requests\SecondFactorRequest;
use App\Modules\Auth\Processors\VerifySecondFactor;
use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\RedirectResponse;

final class SecondFactorFormController
{
    public function __construct(
        private readonly VerifySecondFactor $processor,
        private readonly StatefulGuard $guard,
        private readonly UserProvider $users,
    ) {}

    public function __invoke(SecondFactorRequest $request): RedirectResponse
    {
        $pending = SecondFactorSession::pendingUser($request);

        if ($pending === null) {
            return redirect()->route('sign-in');
        }

        $this->processor->process(new MfaChallenge($pending, $request->code()));

        $user = $this->users->retrieveById($pending);

        if ($user === null) {
            return redirect()->route('sign-in')->with('error', 'That account no longer exists.');
        }

        $this->guard->login($user);
        $request->session()->regenerate();
        SecondFactorSession::markVerified($request);

        return redirect()->intended(route('dashboard'));
    }
}
