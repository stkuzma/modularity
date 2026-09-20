<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SignOutFormController
{
    public function __construct(private readonly StatefulGuard $guard) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $this->guard->logout();

        SecondFactorSession::clear($request);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('sign-in')->with('status', 'You have been signed out.');
    }
}
