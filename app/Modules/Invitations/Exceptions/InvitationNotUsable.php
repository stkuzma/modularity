<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Exceptions;

use App\Core\Exceptions\ConflictException;

final class InvitationNotUsable extends ConflictException
{
    public static function expired(): self
    {
        return new self(
            message: 'That invitation has expired.',
            domainCode: 'invitations.expired',
        );
    }

    public static function alreadyAccepted(): self
    {
        return new self(
            message: 'That invitation has already been used.',
            domainCode: 'invitations.already_accepted',
        );
    }
}
