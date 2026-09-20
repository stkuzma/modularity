<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Models\User;

/**
 * @implements Processor<int, User>
 */
final readonly class ShowUser implements Processor
{
    public function __construct(private UserRepository $users) {}

    public function process(mixed $input): User
    {
        return $this->users->findById($input)
            ?? throw NotFoundException::resource('users', $input);
    }
}
