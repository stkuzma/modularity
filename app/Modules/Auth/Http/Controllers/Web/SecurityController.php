<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Core\Auth\ActorId;
use App\Modules\Auth\Contracts\MfaRepository;
use App\Modules\Auth\Contracts\TokenRepository;
use App\Modules\Auth\Presenters\TokenPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class SecurityController
{
    public function __construct(
        private readonly MfaRepository $mfa,
        private readonly TokenRepository $tokens,
        private readonly TokenPresenter $presenter,
    ) {}

    public function __invoke(Request $request): View
    {
        $userId = ActorId::required($request->user());
        $secret = $this->mfa->findFor($userId);

        return view('auth::security', [
            'enrolled' => $secret !== null,
            'confirmed' => $secret?->isConfirmed() ?? false,
            'tokens' => $this->presenter->collection($this->tokens->listForUser($userId)),
        ]);
    }
}
