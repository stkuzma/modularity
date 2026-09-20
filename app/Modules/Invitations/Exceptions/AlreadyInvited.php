<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Exceptions;

use App\Core\Exceptions\ConflictException;

final class AlreadyInvited extends ConflictException
{
    public static function for(string $email): self
    {
        return new self(
            message: 'That address already has an invitation waiting.',
            domainCode: 'invitations.already_invited',
            details: ['email' => $email],
        );
    }
}
