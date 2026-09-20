<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Unit;

use App\Core\Exceptions\NotFoundException;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Events\UserSuspended;
use App\Modules\Users\Exceptions\EmailAlreadyTaken;
use App\Modules\Users\Processors\DeleteUser;
use App\Modules\Users\Processors\ShowUser;
use App\Modules\Users\Processors\UpdateUser;
use App\Modules\Users\Tests\Support\InMemoryUserRepository;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UpdateUserTest extends TestCase
{
    #[Test]
    public function it_updates_only_what_it_is_given(): void
    {
        $users = new InMemoryUserRepository;
        $ada = $users->create(new CreateUserData('Ada', 'ada@example.com', 'secret-password'));

        $updated = (new UpdateUser($users, new Dispatcher))->process([
            'id' => $ada->id,
            'data' => new UpdateUserData(name: 'Ada Lovelace'),
        ]);

        $this->assertSame('Ada Lovelace', $updated->name);
        $this->assertSame('ada@example.com', $updated->email);
    }

    #[Test]
    public function it_allows_a_user_to_keep_its_own_email(): void
    {
        $users = new InMemoryUserRepository;
        $ada = $users->create(new CreateUserData('Ada', 'ada@example.com', 'secret-password'));

        $updated = (new UpdateUser($users, new Dispatcher))->process([
            'id' => $ada->id,
            'data' => new UpdateUserData(email: 'ada@example.com', name: 'Ada L'),
        ]);

        $this->assertSame('Ada L', $updated->name);
    }

    #[Test]
    public function it_refuses_an_email_another_user_already_has(): void
    {
        $users = new InMemoryUserRepository;
        $ada = $users->create(new CreateUserData('Ada', 'ada@example.com', 'secret-password'));
        $users->create(new CreateUserData('Grace', 'grace@example.com', 'secret-password'));

        $this->expectException(EmailAlreadyTaken::class);

        (new UpdateUser($users, new Dispatcher))->process([
            'id' => $ada->id,
            'data' => new UpdateUserData(email: 'grace@example.com'),
        ]);
    }

    #[Test]
    public function an_empty_update_is_a_no_op(): void
    {
        $users = new InMemoryUserRepository;
        $ada = $users->create(new CreateUserData('Ada', 'ada@example.com', 'secret-password'));

        $updated = (new UpdateUser($users, new Dispatcher))->process([
            'id' => $ada->id,
            'data' => new UpdateUserData,
        ]);

        $this->assertSame('Ada', $updated->name);
    }

    #[Test]
    public function suspending_a_user_announces_it(): void
    {
        $users = new InMemoryUserRepository;
        $ada = $users->create(new CreateUserData('Ada', 'ada@example.com', 'secret-password'));

        $events = new Dispatcher;
        $heard = [];
        $events->listen(UserSuspended::class, function (UserSuspended $event) use (&$heard): void {
            $heard[] = $event->userId;
        });

        (new UpdateUser($users, $events))->process([
            'id' => $ada->id,
            'data' => new UpdateUserData(status: UserStatus::Suspended),
        ]);

        $this->assertSame([$ada->id], $heard);
    }

    #[Test]
    public function an_already_suspended_user_is_not_announced_again(): void
    {
        $users = new InMemoryUserRepository;
        $ada = $users->create(new CreateUserData('Ada', 'ada@example.com', 'secret', UserStatus::Suspended));

        $events = new Dispatcher;
        $heard = 0;
        $events->listen(UserSuspended::class, function () use (&$heard): void {
            $heard++;
        });

        (new UpdateUser($users, $events))->process([
            'id' => $ada->id,
            'data' => new UpdateUserData(name: 'Ada L', status: UserStatus::Suspended),
        ]);

        $this->assertSame(0, $heard);
    }

    #[Test]
    public function showing_an_unknown_user_is_a_not_found(): void
    {
        $this->expectException(NotFoundException::class);

        (new ShowUser(new InMemoryUserRepository))->process(404);
    }

    #[Test]
    public function the_not_found_failure_names_the_resource(): void
    {
        $exception = NotFoundException::resource('users', 404);

        $this->assertSame('users.not_found', $exception->errorCode());
        $this->assertSame(404, $exception->statusCode());
    }

    #[Test]
    public function deleting_an_unknown_user_is_a_not_found(): void
    {
        $this->expectException(NotFoundException::class);

        (new DeleteUser(new InMemoryUserRepository))->process(404);
    }

    #[Test]
    public function it_deletes_a_known_user(): void
    {
        $users = new InMemoryUserRepository;
        $ada = $users->create(new CreateUserData('Ada', 'ada@example.com', 'secret-password'));

        (new DeleteUser($users))->process($ada->id);

        $this->assertNull($users->findById($ada->id));
    }
}
