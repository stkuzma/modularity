<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Access\AccessChecker;
use App\Core\Exceptions\UnauthorizedException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController
{
    public function __construct(private readonly AccessChecker $access) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user() ?? throw new UnauthorizedException;

        return view('dashboard', [
            'identity' => [
                'name' => $user->getAttribute('name'),
                'email' => $user->getAttribute('email'),
            ],
            'permissions' => $this->access->permissionsFor($user),
        ]);
    }
}
