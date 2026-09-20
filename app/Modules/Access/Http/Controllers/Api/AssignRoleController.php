<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Access\Data\RoleAssignment;
use App\Modules\Access\Http\Requests\RoleAssignmentRequest;
use App\Modules\Access\Processors\AssignRole;
use Illuminate\Http\JsonResponse;

final class AssignRoleController extends ApiController
{
    public function __construct(private readonly AssignRole $processor) {}

    public function __invoke(RoleAssignmentRequest $request, int $role): JsonResponse
    {
        $this->processor->process(new RoleAssignment($role, $request->userId()));

        return $this->noContent();
    }
}
