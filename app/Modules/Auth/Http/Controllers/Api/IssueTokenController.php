<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Auth\ActorId;
use App\Core\Http\ApiController;
use App\Modules\Auth\Data\TokenRequest;
use App\Modules\Auth\Http\Requests\IssueTokenRequest;
use App\Modules\Auth\Presenters\TokenPresenter;
use App\Modules\Auth\Processors\IssueToken;
use Illuminate\Http\JsonResponse;

final class IssueTokenController extends ApiController
{
    public function __construct(
        private readonly IssueToken $processor,
        private readonly TokenPresenter $presenter,
    ) {}

    public function __invoke(IssueTokenRequest $request): JsonResponse
    {
        $issued = $this->processor->process(new TokenRequest(
            userId: ActorId::required($request->user()),
            name: $request->tokenName(),
            expiresInDays: $request->expiresInDays(),
        ));

        return $this->created([
            ...$this->presenter->present($issued->token),
            // The only time this value is ever readable.
            'token' => $issued->plainText,
        ]);
    }
}
