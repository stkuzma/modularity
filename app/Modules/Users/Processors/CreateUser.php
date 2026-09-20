<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Pipeline\Processor;
use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Exceptions\EmailAlreadyTaken;
use App\Modules\Users\Models\User;

/**
 * @implements Processor<CreateUserData, User>
 */
final readonly class CreateUser implements Processor
{
    public function __construct(private UserRepository $users) {}

    public function process(mixed $input): User
    {
        if ($this->users->existsByEmail($input->email)) {
            throw EmailAlreadyTaken::for($input->email);
        }

        return $this->users->create($input);
    }
}
