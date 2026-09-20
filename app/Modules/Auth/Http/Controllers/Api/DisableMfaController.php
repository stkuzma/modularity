<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Auth\ActorId;
use App\Core\Http\ApiController;
use App\Modules\Auth\Data\MfaChallenge;
use App\Modules\Auth\Http\Requests\SecondFactorRequest;
use App\Modules\Auth\Processors\DisableMfa;
use Illuminate\Http\JsonResponse;

final class DisableMfaController extends ApiController
{
    public function __construct(private readonly DisableMfa $processor) {}

    public function __invoke(SecondFactorRequest $request): JsonResponse
    {
        $this->processor->process(new MfaChallenge(
            userId: ActorId::required($request->user()),
            code: $request->code(),
        ));

        return $this->noContent();
    }
}
