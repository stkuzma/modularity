<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Controllers\Web;

use App\Modules\Invitations\Presenters\InvitationPresenter;
use App\Modules\Invitations\Processors\ListInvitations;
use Illuminate\Contracts\View\View;

final class InvitationScreenController
{
    public function __construct(
        private readonly ListInvitations $processor,
        private readonly InvitationPresenter $presenter,
    ) {}

    public function __invoke(): View
    {
        return view('invitations::index', [
            'invitations' => $this->presenter->collection($this->processor->process()),
        ]);
    }
}
