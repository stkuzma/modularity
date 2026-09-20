<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Modules\Users\Contracts\Accounts;
use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Exceptions\EmailAlreadyTaken;

final readonly class AccountService implements Accounts
{
    public function __construct(private UserRepository $users) {}

    public function isRegistered(string $email): bool
    {
        return $this->users->existsByEmail($email);
    }

    public function register(CreateUserData $data): int
    {
        if ($this->users->existsByEmail($data->email)) {
            throw EmailAlreadyTaken::for($data->email);
        }

        return $this->users->create($data)->id;
    }
}
