<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Controllers\Web;

use App\Modules\Invitations\Processors\RevokeInvitation;
use Illuminate\Http\RedirectResponse;

final class RevokeInvitationFormController
{
    public function __construct(private readonly RevokeInvitation $processor) {}

    public function __invoke(int $invitation): RedirectResponse
    {
        $this->processor->process($invitation);

        return redirect()->route('invitations.index')->with('status', 'Invitation revoked.');
    }
}
