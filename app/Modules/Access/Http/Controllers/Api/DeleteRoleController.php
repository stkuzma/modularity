<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Access\Processors\DeleteRole;
use Illuminate\Http\JsonResponse;

final class DeleteRoleController extends ApiController
{
    public function __construct(private readonly DeleteRole $processor) {}

    public function __invoke(int $role): JsonResponse
    {
        $this->processor->process($role);

        return $this->noContent();
    }
}
