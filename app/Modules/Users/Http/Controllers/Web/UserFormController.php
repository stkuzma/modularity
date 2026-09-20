<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Web;

use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Presenters\UserPresenter;
use App\Modules\Users\Processors\ShowUser;
use Illuminate\Contracts\View\View;

final class UserFormController
{
    public function __construct(
        private readonly ShowUser $processor,
        private readonly UserPresenter $presenter,
    ) {}

    public function __invoke(?int $user = null): View
    {
        return view('users::form', [
            'user' => $user === null ? null : $this->presenter->present($this->processor->process($user)),
            'statuses' => UserStatus::cases(),
        ]);
    }
}
