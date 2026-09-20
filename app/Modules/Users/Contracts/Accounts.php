<?php

declare(strict_types=1);

namespace App\Modules\Users\Contracts;

use App\Modules\Users\Data\CreateUserData;

/**
 * What this module offers other modules: capabilities, not storage.
 *
 * UserRepository stays internal. A module that consumed it would be coupled to
 * the shape of this one's persistence rather than to what it can do.
 */
interface Accounts
{
    public function isRegistered(string $email): bool;

    /** @return int the new account's id */
    public function register(CreateUserData $data): int;
}
