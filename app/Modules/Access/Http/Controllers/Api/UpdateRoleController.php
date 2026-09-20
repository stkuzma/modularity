<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Access\Http\Requests\RoleRequest;
use App\Modules\Access\Presenters\RolePresenter;
use App\Modules\Access\Processors\UpdateRole;
use Illuminate\Http\JsonResponse;

final class UpdateRoleController extends ApiController
{
    public function __construct(
        private readonly UpdateRole $processor,
        private readonly RolePresenter $presenter,
    ) {}

    public function __invoke(RoleRequest $request, int $role): JsonResponse
    {
        $updated = $this->processor->process(['id' => $role, 'data' => $request->toData()]);

        return $this->ok($this->presenter->present($updated));
    }
}
