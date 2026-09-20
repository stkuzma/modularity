<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Access\Http\Requests\RoleRequest;
use App\Modules\Access\Presenters\RolePresenter;
use App\Modules\Access\Processors\CreateRole;
use Illuminate\Http\JsonResponse;

final class CreateRoleController extends ApiController
{
    public function __construct(
        private readonly CreateRole $processor,
        private readonly RolePresenter $presenter,
    ) {}

    public function __invoke(RoleRequest $request): JsonResponse
    {
        return $this->created($this->presenter->present($this->processor->process($request->toData())));
    }
}
