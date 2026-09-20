<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

final readonly class Credentials
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}

    /**
     * @return array<string, string>
     */
    public function forProvider(): array
    {
        return ['email' => $this->email, 'password' => $this->password];
    }
}
