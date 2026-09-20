<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Web;

use App\Modules\Access\Processors\DeleteRole;
use Illuminate\Http\RedirectResponse;

final class DeleteRoleFormController
{
    public function __construct(private readonly DeleteRole $processor) {}

    public function __invoke(int $role): RedirectResponse
    {
        $this->processor->process($role);

        return redirect()->route('roles.index')->with('status', 'The role was deleted.');
    }
}
