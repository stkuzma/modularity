<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Controllers\Web;

use App\Modules\Invitations\Http\Requests\SendInvitationRequest;
use App\Modules\Invitations\Processors\SendInvitation;
use Illuminate\Http\RedirectResponse;

final class SendInvitationFormController
{
    public function __construct(private readonly SendInvitation $processor) {}

    public function __invoke(SendInvitationRequest $request): RedirectResponse
    {
        $sent = $this->processor->process($request->toData());

        // No mailer here; the link comes back once for the inviter to pass on.
        return redirect()->route('invitations.index')->with([
            'status' => "Invitation created for {$sent->invitation->email}.",
            'invite_url' => route('invitations.accept', ['token' => $sent->token]),
        ]);
    }
}
