<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Exceptions\UnauthorizedException;
use App\Core\Http\ApiController;
use App\Modules\Auth\Data\MfaChallenge;
use App\Modules\Auth\Http\Requests\SecondFactorRequest;
use App\Modules\Auth\Presenters\IdentityPresenter;
use App\Modules\Auth\Processors\VerifySecondFactor;
use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\JsonResponse;

final class VerifySecondFactorController extends ApiController
{
    public function __construct(
        private readonly VerifySecondFactor $processor,
        private readonly IdentityPresenter $presenter,
        private readonly StatefulGuard $guard,
        private readonly UserProvider $users,
    ) {}

    public function __invoke(SecondFactorRequest $request): JsonResponse
    {
        $pending = SecondFactorSession::pendingUser($request);

        if ($pending === null) {
            throw new UnauthorizedException(
                message: 'There is no sign-in waiting for a second factor.',
                domainCode: 'auth.no_pending_challenge',
            );
        }

        $this->processor->process(new MfaChallenge($pending, $request->code()));

        $user = $this->users->retrieveById($pending);

        if ($user === null) {
            throw new UnauthorizedException(
                message: 'That account no longer exists.',
                domainCode: 'auth.account_missing',
            );
        }

        $this->guard->login($user);
        $request->session()->regenerate();
        SecondFactorSession::markVerified($request);

        return $this->ok([
            'status' => 'signed_in',
            'user' => $this->presenter->present($user),
        ]);
    }
}
