<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Auth\ActorId;
use App\Core\Http\ApiController;
use App\Modules\Auth\Processors\RevokeToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RevokeTokenController extends ApiController
{
    public function __construct(private readonly RevokeToken $processor) {}

    public function __invoke(Request $request, int $token): JsonResponse
    {
        $this->processor->process([
            'userId' => ActorId::required($request->user()),
            'tokenId' => $token,
        ]);

        return $this->noContent();
    }
}
