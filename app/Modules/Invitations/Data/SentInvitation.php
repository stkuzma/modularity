<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Data;

use App\Modules\Invitations\Models\Invitation;

/** The plaintext token exists once, on the way out. */
final readonly class SentInvitation
{
    public function __construct(
        public Invitation $invitation,
        public string $token,
    ) {}
}
