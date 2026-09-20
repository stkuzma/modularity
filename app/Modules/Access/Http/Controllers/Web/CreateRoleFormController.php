<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Web;

use App\Modules\Access\Http\Requests\RoleRequest;
use App\Modules\Access\Processors\CreateRole;
use Illuminate\Http\RedirectResponse;

final class CreateRoleFormController
{
    public function __construct(private readonly CreateRole $processor) {}

    public function __invoke(RoleRequest $request): RedirectResponse
    {
        $role = $this->processor->process($request->toData());

        return redirect()->route('roles.edit', $role->id)->with('status', "{$role->label} was created.");
    }
}
