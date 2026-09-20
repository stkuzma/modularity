<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Access\AccessChecker;
use App\Core\Exceptions\UnauthorizedException;
use App\Core\Http\ApiController;
use App\Modules\Auth\Presenters\IdentityPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CurrentIdentityController extends ApiController
{
    public function __construct(
        private readonly IdentityPresenter $presenter,
        private readonly AccessChecker $access,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user() ?? throw new UnauthorizedException;

        return $this->ok([
            ...$this->presenter->present($user),
            'permissions' => $this->access->permissionsFor($user),
        ]);
    }
}
