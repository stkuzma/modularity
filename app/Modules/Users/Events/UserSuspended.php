<?php

declare(strict_types=1);

namespace App\Modules\Users\Events;

final readonly class UserSuspended
{
    public function __construct(public int $userId) {}
}
