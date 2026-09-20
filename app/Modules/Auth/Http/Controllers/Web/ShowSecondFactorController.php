<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ShowSecondFactorController
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (SecondFactorSession::pendingUser($request) === null) {
            return redirect()->route('sign-in');
        }

        return view('auth::second-factor');
    }
}
