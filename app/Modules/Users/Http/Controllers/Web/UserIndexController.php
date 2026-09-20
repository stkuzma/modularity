<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Web;

use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Http\Requests\ListUsersRequest;
use App\Modules\Users\Presenters\UserPresenter;
use App\Modules\Users\Processors\ListUsers;
use Illuminate\Contracts\View\View;

final class UserIndexController
{
    public function __construct(
        private readonly ListUsers $processor,
        private readonly UserPresenter $presenter,
    ) {}

    public function __invoke(ListUsersRequest $request): View
    {
        $filter = $request->toFilter();
        $page = $this->processor->process($filter);

        return view('users::index', [
            'users' => $this->presenter->collection($page->items()),
            'page' => $page->withQueryString(),
            'filter' => $filter,
            'statuses' => UserStatus::cases(),
        ]);
    }
}
