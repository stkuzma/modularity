<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Web;

use App\Modules\Access\Data\PermissionSync;
use App\Modules\Access\Http\Requests\RoleRequest;
use App\Modules\Access\Processors\SyncRolePermissions;
use App\Modules\Access\Processors\UpdateRole;
use Illuminate\Http\RedirectResponse;

final class UpdateRoleFormController
{
    public function __construct(
        private readonly UpdateRole $updateRole,
        private readonly SyncRolePermissions $syncPermissions,
    ) {}

    public function __invoke(RoleRequest $request, int $role): RedirectResponse
    {
        $updated = $this->updateRole->process(['id' => $role, 'data' => $request->toData()]);

        $this->syncPermissions->process(new PermissionSync($role, $request->permissionNames()));

        return redirect()->route('roles.index')->with('status', "{$updated->label} was saved.");
    }
}
