<?php

declare(strict_types=1);

namespace App\Modules\Users\Exceptions;

use App\Core\Exceptions\ConflictException;

final class EmailAlreadyTaken extends ConflictException
{
    public static function for(string $email): self
    {
        return new self(
            message: 'That email address is already registered.',
            domainCode: 'users.email_taken',
            details: ['email' => $email],
        );
    }
}
