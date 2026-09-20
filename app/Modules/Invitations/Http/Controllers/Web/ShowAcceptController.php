<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Controllers\Web;

use Illuminate\Contracts\View\View;

final class ShowAcceptController
{
    public function __invoke(string $token): View
    {
        return view('invitations::accept', ['token' => $token]);
    }
}
