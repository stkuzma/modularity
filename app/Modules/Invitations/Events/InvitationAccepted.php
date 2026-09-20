<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Events;

final readonly class InvitationAccepted
{
    public function __construct(
        public int $invitationId,
        public int $userId,
        public string $email,
    ) {}
}
