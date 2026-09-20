<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class ConflictException extends DomainException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message = 'That action conflicts with the current state.',
        private readonly string $domainCode = 'core.conflict',
        private readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->domainCode;
    }

    public function statusCode(): int
    {
        return 409;
    }

    public function details(): array
    {
        return $this->details;
    }
}
