<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Invitations\Contracts\InvitationRepository;
use App\Modules\Invitations\Data\AcceptData;
use App\Modules\Invitations\Events\InvitationAccepted;
use App\Modules\Invitations\Exceptions\InvitationNotUsable;
use App\Modules\Invitations\Models\Invitation;
use App\Modules\Users\Contracts\Accounts;
use App\Modules\Users\Data\CreateUserData;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;

/**
 * @implements Processor<AcceptData, Invitation>
 */
final readonly class AcceptInvitation implements Processor
{
    public function __construct(
        private InvitationRepository $invitations,
        private Accounts $accounts,
        private AuditTrail $audit,
        private Dispatcher $events,
    ) {}

    public function process(mixed $input): Invitation
    {
        $invitation = $this->invitations->findByTokenHash(hash('sha256', $input->token))
            ?? throw NotFoundException::resource('invitations', 'token');

        if ($invitation->isAccepted()) {
            throw InvitationNotUsable::alreadyAccepted();
        }

        if ($invitation->hasExpired()) {
            throw InvitationNotUsable::expired();
        }

        $userId = DB::transaction(function () use ($invitation, $input): int {
            $userId = $this->accounts->register(new CreateUserData(
                name: $input->name,
                email: $invitation->email,
                password: $input->password,
            ));

            $this->invitations->markAccepted($invitation);

            return $userId;
        });

        $this->audit->record('invitations.accepted', 'invitation', $invitation->id, ['user_id' => $userId]);
        $this->events->dispatch(new InvitationAccepted($invitation->id, $userId, $invitation->email));

        return $invitation;
    }
}
