<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Auth\ActorId;
use App\Core\Http\ApiController;
use App\Modules\Auth\Http\Requests\SignInRequest;
use App\Modules\Auth\Presenters\IdentityPresenter;
use App\Modules\Auth\Processors\SignIn;
use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;

final class SignInController extends ApiController
{
    public function __construct(
        private readonly SignIn $processor,
        private readonly IdentityPresenter $presenter,
        private readonly StatefulGuard $guard,
    ) {}

    public function __invoke(SignInRequest $request): JsonResponse
    {
        $result = $this->processor->process($request->toCredentials());

        if ($result->requiresSecondFactor) {
            // Not signed in yet; the session holds only the pending identity.
            SecondFactorSession::beginChallenge($request, ActorId::required($result->user));

            return $this->ok(['status' => 'second_factor_required']);
        }

        $this->guard->login($result->user, $request->toCredentials()->remember);
        $request->session()->regenerate();
        SecondFactorSession::markVerified($request);

        return $this->ok([
            'status' => 'signed_in',
            'user' => $this->presenter->present($result->user),
        ]);
    }
}
