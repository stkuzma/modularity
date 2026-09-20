<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Access\Data\PermissionSync;
use App\Modules\Access\Http\Requests\SyncPermissionsRequest;
use App\Modules\Access\Presenters\RolePresenter;
use App\Modules\Access\Processors\SyncRolePermissions;
use Illuminate\Http\JsonResponse;

final class SyncRolePermissionsController extends ApiController
{
    public function __construct(
        private readonly SyncRolePermissions $processor,
        private readonly RolePresenter $presenter,
    ) {}

    public function __invoke(SyncPermissionsRequest $request, int $role): JsonResponse
    {
        $updated = $this->processor->process(
            new PermissionSync($role, $request->permissionNames()),
        );

        return $this->ok($this->presenter->present($updated));
    }
}
