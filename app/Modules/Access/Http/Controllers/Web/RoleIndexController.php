<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Controllers\Web;

use App\Modules\Access\Presenters\RolePresenter;
use App\Modules\Access\Processors\ListRoles;
use Illuminate\Contracts\View\View;

final class RoleIndexController
{
    public function __construct(
        private readonly ListRoles $processor,
        private readonly RolePresenter $presenter,
    ) {}

    public function __invoke(): View
    {
        return view('access::roles.index', [
            'roles' => $this->presenter->collection($this->processor->process()),
        ]);
    }
}
