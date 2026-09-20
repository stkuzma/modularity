<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use Illuminate\Contracts\View\View;

final class ShowSignInController
{
    public function __invoke(): View
    {
        return view('auth::sign-in');
    }
}
