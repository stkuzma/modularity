<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class ForbiddenException extends DomainException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message = 'You are not allowed to perform this action.',
        private readonly string $domainCode = 'core.forbidden',
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
        return 403;
    }

    public function details(): array
    {
        return $this->details;
    }
}
