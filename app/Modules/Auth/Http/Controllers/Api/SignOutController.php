<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SignOutController extends ApiController
{
    public function __construct(private readonly StatefulGuard $guard) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->guard->logout();

        SecondFactorSession::clear($request);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->noContent();
    }
}
