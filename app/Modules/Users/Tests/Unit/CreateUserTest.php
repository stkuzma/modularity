<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Unit;

use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Exceptions\EmailAlreadyTaken;
use App\Modules\Users\Processors\CreateUser;
use App\Modules\Users\Tests\Support\InMemoryUserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateUserTest extends TestCase
{
    #[Test]
    public function it_creates_a_user(): void
    {
        $users = new InMemoryUserRepository;

        $user = (new CreateUser($users))->process(
            new CreateUserData('Ada Lovelace', 'ada@example.com', 'secret-password'),
        );

        $this->assertSame('Ada Lovelace', $user->name);
        $this->assertSame('ada@example.com', $user->email);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertTrue($users->existsByEmail('ada@example.com'));
    }

    #[Test]
    public function it_refuses_a_duplicate_email(): void
    {
        $users = new InMemoryUserRepository;
        $processor = new CreateUser($users);
        $data = new CreateUserData('Ada Lovelace', 'ada@example.com', 'secret-password');

        $processor->process($data);

        $this->expectException(EmailAlreadyTaken::class);

        $processor->process($data);
    }

    #[Test]
    public function the_duplicate_email_failure_carries_a_stable_code_and_status(): void
    {
        $exception = EmailAlreadyTaken::for('ada@example.com');

        $this->assertSame('users.email_taken', $exception->errorCode());
        $this->assertSame(409, $exception->statusCode());
        $this->assertSame(['email' => 'ada@example.com'], $exception->details());
    }

    #[Test]
    public function it_honours_an_explicit_status(): void
    {
        $users = new InMemoryUserRepository;

        $user = (new CreateUser($users))->process(new CreateUserData(
            name: 'Grace Hopper',
            email: 'grace@example.com',
            password: 'secret-password',
            status: UserStatus::Suspended,
        ));

        $this->assertSame(UserStatus::Suspended, $user->status);
        $this->assertFalse($user->status->canSignIn());
    }
}
