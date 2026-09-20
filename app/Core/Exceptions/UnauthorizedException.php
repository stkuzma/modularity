<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class UnauthorizedException extends DomainException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message = 'Authentication is required.',
        private readonly string $domainCode = 'core.unauthorized',
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
        return 401;
    }

    public function details(): array
    {
        return $this->details;
    }
}
