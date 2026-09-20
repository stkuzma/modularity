<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Invitations\Processors\RevokeInvitation;
use Illuminate\Http\JsonResponse;

final class RevokeInvitationController extends ApiController
{
    public function __construct(private readonly RevokeInvitation $processor) {}

    public function __invoke(int $invitation): JsonResponse
    {
        $this->processor->process($invitation);

        return $this->noContent();
    }
}
