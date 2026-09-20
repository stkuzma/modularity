<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Auth\ActorId;
use App\Core\Http\ApiController;
use App\Modules\Auth\Data\MfaChallenge;
use App\Modules\Auth\Http\Requests\SecondFactorRequest;
use App\Modules\Auth\Processors\ConfirmMfaEnrolment;
use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Http\JsonResponse;

final class ConfirmMfaController extends ApiController
{
    public function __construct(private readonly ConfirmMfaEnrolment $processor) {}

    public function __invoke(SecondFactorRequest $request): JsonResponse
    {
        $this->processor->process(new MfaChallenge(
            userId: ActorId::required($request->user()),
            code: $request->code(),
        ));

        // This session just answered a challenge.
        SecondFactorSession::markVerified($request);

        return $this->ok(['status' => 'enabled']);
    }
}
