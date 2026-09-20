<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Events\UserSuspended;
use App\Modules\Users\Exceptions\EmailAlreadyTaken;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * @phpstan-type UpdateUserInput array{id: int, data: UpdateUserData}
 *
 * @implements Processor<UpdateUserInput, User>
 */
final readonly class UpdateUser implements Processor
{
    public function __construct(
        private UserRepository $users,
        private Dispatcher $events,
    ) {}

    public function process(mixed $input): User
    {
        $user = $this->users->findById($input['id'])
            ?? throw NotFoundException::resource('users', $input['id']);

        $data = $input['data'];

        if ($data->email !== null && $this->users->existsByEmail($data->email, $user->id)) {
            throw EmailAlreadyTaken::for($data->email);
        }

        if ($data->isEmpty()) {
            return $user;
        }

        $wasActive = $user->status !== UserStatus::Suspended;
        $updated = $this->users->update($user, $data);

        if ($wasActive && $updated->status === UserStatus::Suspended) {
            $this->events->dispatch(new UserSuspended($updated->id));
        }

        return $updated;
    }
}
