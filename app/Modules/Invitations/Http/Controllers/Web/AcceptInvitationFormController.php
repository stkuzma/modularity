<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Controllers\Web;

use App\Modules\Invitations\Http\Requests\AcceptInvitationRequest;
use App\Modules\Invitations\Processors\AcceptInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

final class AcceptInvitationFormController
{
    public function __construct(private readonly AcceptInvitation $processor) {}

    public function __invoke(AcceptInvitationRequest $request): RedirectResponse
    {
        $data = $request->toData();

        $this->processor->process($data);

        $next = Route::has('sign-in') ? route('sign-in') : '/';

        return redirect()->to($next)->with('status', "Welcome, {$data->name}. You can sign in now.");
    }
}
