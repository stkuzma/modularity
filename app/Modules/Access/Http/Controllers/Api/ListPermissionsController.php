<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Access\Contracts\PermissionRepository;
use App\Modules\Access\Presenters\PermissionPresenter;
use Illuminate\Http\JsonResponse;

final class ListPermissionsController extends ApiController
{
    public function __construct(
        private readonly PermissionRepository $permissions,
        private readonly PermissionPresenter $presenter,
    ) {}

    public function __invoke(): JsonResponse
    {
        return $this->ok($this->presenter->collection($this->permissions->all()));
    }
}
