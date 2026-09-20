<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Auth\ActorId;
use App\Core\Http\ApiController;
use App\Modules\Auth\Contracts\TokenRepository;
use App\Modules\Auth\Presenters\TokenPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListTokensController extends ApiController
{
    public function __construct(
        private readonly TokenRepository $tokens,
        private readonly TokenPresenter $presenter,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $userId = ActorId::required($request->user());

        return $this->ok($this->presenter->collection($this->tokens->listForUser($userId)));
    }
}
