<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Access\Presenters\RolePresenter;
use App\Modules\Access\Processors\ListRoles;
use Illuminate\Http\JsonResponse;

final class ListRolesController extends ApiController
{
    public function __construct(
        private readonly ListRoles $processor,
        private readonly RolePresenter $presenter,
    ) {}

    public function __invoke(): JsonResponse
    {
        return $this->ok($this->presenter->collection($this->processor->process()));
    }
}
