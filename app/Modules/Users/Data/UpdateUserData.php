<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Modules\Users\Enums\UserStatus;

final readonly class UpdateUserData
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?UserStatus $status = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function changes(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'status' => $this->status,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function isEmpty(): bool
    {
        return $this->changes() === [];
    }
}
