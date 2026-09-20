<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Core\Exceptions\ConflictException;

final class MfaStateConflict extends ConflictException
{
    public static function alreadyConfirmed(): self
    {
        return new self(
            message: 'Two-factor authentication is already enabled for this account.',
            domainCode: 'auth.mfa_already_enabled',
        );
    }

    public static function notEnrolled(): self
    {
        return new self(
            message: 'Two-factor authentication has not been set up for this account.',
            domainCode: 'auth.mfa_not_enrolled',
        );
    }
}
