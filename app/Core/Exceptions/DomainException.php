<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
    abstract public function errorCode(): string;

    abstract public function statusCode(): int;

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return [];
    }
}
