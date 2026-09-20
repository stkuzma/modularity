<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Core\Exceptions\ForbiddenException;

final class SignInNotPermitted extends ForbiddenException
{
    public static function because(string $reason): self
    {
        return new self(
            message: 'This account cannot sign in.',
            domainCode: 'auth.sign_in_not_permitted',
            details: ['reason' => $reason],
        );
    }
}
