<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Invitations\Contracts\InvitationRepository;
use App\Modules\Invitations\Exceptions\InvitationNotUsable;

/**
 * @implements Processor<int, null>
 */
final readonly class RevokeInvitation implements Processor
{
    public function __construct(
        private InvitationRepository $invitations,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): null
    {
        $invitation = $this->invitations->findById($input)
            ?? throw NotFoundException::resource('invitations', $input);

        if ($invitation->isAccepted()) {
            throw InvitationNotUsable::alreadyAccepted();
        }

        $this->invitations->delete($invitation);

        $this->audit->record('invitations.revoked', 'invitation', $input, ['email' => $invitation->email]);

        return null;
    }
}
