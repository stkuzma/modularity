<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class NotFoundException extends DomainException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message = 'The requested resource was not found.',
        private readonly string $domainCode = 'core.not_found',
        private readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public static function resource(string $resource, string|int $id): self
    {
        return new self(
            message: "No {$resource} found for the given identifier.",
            domainCode: "{$resource}.not_found",
            details: ['id' => $id],
        );
    }

    public function errorCode(): string
    {
        return $this->domainCode;
    }

    public function statusCode(): int
    {
        return 404;
    }

    public function details(): array
    {
        return $this->details;
    }
}
