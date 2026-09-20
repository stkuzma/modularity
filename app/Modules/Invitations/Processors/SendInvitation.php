<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Pipeline\Processor;
use App\Modules\Invitations\Contracts\InvitationRepository;
use App\Modules\Invitations\Data\InviteData;
use App\Modules\Invitations\Data\SentInvitation;
use App\Modules\Invitations\Exceptions\AlreadyInvited;
use App\Modules\Users\Contracts\Accounts;
use Illuminate\Support\Str;

/**
 * @implements Processor<InviteData, SentInvitation>
 */
final readonly class SendInvitation implements Processor
{
    public function __construct(
        private InvitationRepository $invitations,
        private Accounts $accounts,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): SentInvitation
    {
        if ($this->accounts->isRegistered($input->email) || $this->invitations->hasPendingFor($input->email)) {
            throw AlreadyInvited::for($input->email);
        }

        $token = Str::random(48);
        $invitation = $this->invitations->create($input, hash('sha256', $token));

        $this->audit->record('invitations.sent', 'invitation', $invitation->id, ['email' => $input->email]);

        return new SentInvitation($invitation, $token);
    }
}
