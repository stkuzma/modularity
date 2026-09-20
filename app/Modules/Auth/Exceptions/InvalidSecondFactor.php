<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Core\Exceptions\UnauthorizedException;

final class InvalidSecondFactor extends UnauthorizedException
{
    public static function make(): self
    {
        return new self(
            message: 'That code is not valid.',
            domainCode: 'auth.invalid_second_factor',
        );
    }
}
