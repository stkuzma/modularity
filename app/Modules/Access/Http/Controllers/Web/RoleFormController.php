<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Web;

use App\Core\Access\PermissionCatalogue;
use App\Core\Exceptions\NotFoundException;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Presenters\RolePresenter;
use Illuminate\Contracts\View\View;

final class RoleFormController
{
    public function __construct(
        private readonly RoleRepository $roles,
        private readonly RolePresenter $presenter,
        private readonly PermissionCatalogue $catalogue,
    ) {}

    public function __invoke(?int $role = null): View
    {
        $found = $role === null
            ? null
            : ($this->roles->findById($role) ?? throw NotFoundException::resource('roles', $role));

        return view('access::roles.form', [
            'role' => $found === null ? null : $this->presenter->present($found),
            // Grouped by the declaring module.
            'catalogue' => $this->catalogue->groupedByModule(),
        ]);
    }
}
