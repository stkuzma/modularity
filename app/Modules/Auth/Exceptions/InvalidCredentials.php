<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Core\Exceptions\UnauthorizedException;

final class InvalidCredentials extends UnauthorizedException
{
    public static function make(): self
    {
        return new self(
            message: 'Those credentials do not match our records.',
            domainCode: 'auth.invalid_credentials',
        );
    }
}
