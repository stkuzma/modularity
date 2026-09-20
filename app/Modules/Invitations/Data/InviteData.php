<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Data;

final readonly class InviteData
{
    public function __construct(
        public string $email,
        public ?int $invitedBy = null,
        public int $validForDays = 7,
    ) {}
}
