<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Invitations\Http\Requests\SendInvitationRequest;
use App\Modules\Invitations\Presenters\InvitationPresenter;
use App\Modules\Invitations\Processors\SendInvitation;
use Illuminate\Http\JsonResponse;

final class SendInvitationController extends ApiController
{
    public function __construct(
        private readonly SendInvitation $processor,
        private readonly InvitationPresenter $presenter,
    ) {}

    public function __invoke(SendInvitationRequest $request): JsonResponse
    {
        $sent = $this->processor->process($request->toData());

        return $this->created([
            ...$this->presenter->present($sent->invitation),
            'accept_url' => route('invitations.accept', ['token' => $sent->token]),
        ]);
    }
}
