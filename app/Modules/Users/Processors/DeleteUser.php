<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Users\Contracts\UserRepository;

/**
 * @implements Processor<int, null>
 */
final readonly class DeleteUser implements Processor
{
    public function __construct(private UserRepository $users) {}

    public function process(mixed $input): null
    {
        $user = $this->users->findById($input)
            ?? throw NotFoundException::resource('users', $input);

        $this->users->delete($user);

        return null;
    }
}
