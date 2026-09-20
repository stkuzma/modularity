<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Invitations\Presenters\InvitationPresenter;
use App\Modules\Invitations\Processors\ListInvitations;
use Illuminate\Http\JsonResponse;

final class ListInvitationsController extends ApiController
{
    public function __construct(
        private readonly ListInvitations $processor,
        private readonly InvitationPresenter $presenter,
    ) {}

    public function __invoke(): JsonResponse
    {
        return $this->ok($this->presenter->collection($this->processor->process()));
    }
}
