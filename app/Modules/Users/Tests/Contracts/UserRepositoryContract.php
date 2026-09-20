<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Contracts;

use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Data\UserFilter;
use App\Modules\Users\Enums\UserStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

abstract class UserRepositoryContract extends TestCase
{
    #[Test]
    public function it_returns_null_for_an_unknown_id(): void
    {
        $this->assertNull($this->repository()->findById(404));
    }

    #[Test]
    public function it_finds_a_user_it_created(): void
    {
        $repository = $this->repository();

        $created = $repository->create($this->data('ada@example.com'));

        $this->assertGreaterThan(0, $created->id);
        $this->assertSame('ada@example.com', $repository->findById($created->id)?->email);
        $this->assertSame($created->id, $repository->findByEmail('ada@example.com')?->id);
    }

    #[Test]
    public function it_reports_whether_an_email_is_taken(): void
    {
        $repository = $this->repository();
        $repository->create($this->data('ada@example.com'));

        $this->assertTrue($repository->existsByEmail('ada@example.com'));
        $this->assertFalse($repository->existsByEmail('grace@example.com'));
    }

    #[Test]
    public function it_can_ignore_one_user_when_checking_an_email(): void
    {
        $repository = $this->repository();
        $ada = $repository->create($this->data('ada@example.com'));

        $this->assertFalse($repository->existsByEmail('ada@example.com', $ada->id));
        $this->assertTrue($repository->existsByEmail('ada@example.com', $ada->id + 1));
    }

    #[Test]
    public function it_applies_only_the_fields_an_update_carries(): void
    {
        $repository = $this->repository();
        $user = $repository->create($this->data('ada@example.com'));

        $updated = $repository->update($user, new UpdateUserData(name: 'Ada Lovelace'));

        $this->assertSame('Ada Lovelace', $updated->name);
        $this->assertSame('ada@example.com', $updated->email);
        $this->assertSame(UserStatus::Active, $updated->status);
    }

    #[Test]
    public function it_deletes_a_user(): void
    {
        $repository = $this->repository();
        $user = $repository->create($this->data('ada@example.com'));

        $repository->delete($user);

        $this->assertNull($repository->findById($user->id));
    }

    #[Test]
    public function it_filters_by_status(): void
    {
        $repository = $this->repository();
        $repository->create($this->data('ada@example.com'));
        $repository->create($this->data('grace@example.com', UserStatus::Suspended));

        $page = $repository->paginate(new UserFilter(status: UserStatus::Suspended));

        $this->assertSame(1, $page->total());
        $this->assertSame('grace@example.com', $page->items()[0]->email);
    }

    #[Test]
    public function it_filters_by_a_search_term_across_name_and_email(): void
    {
        $repository = $this->repository();
        $repository->create(new CreateUserData('Ada Lovelace', 'ada@example.com', 'secret-password'));
        $repository->create(new CreateUserData('Grace Hopper', 'grace@example.com', 'secret-password'));

        $this->assertSame(1, $repository->paginate(new UserFilter(search: 'Lovelace'))->total());
        $this->assertSame(1, $repository->paginate(new UserFilter(search: 'grace@'))->total());
        $this->assertSame(2, $repository->paginate(new UserFilter(search: 'example.com'))->total());
    }

    #[Test]
    public function it_paginates(): void
    {
        $repository = $this->repository();

        for ($i = 1; $i <= 5; $i++) {
            $repository->create($this->data("user{$i}@example.com"));
        }

        $page = $repository->paginate(new UserFilter(perPage: 2, page: 2));

        $this->assertSame(5, $page->total());
        $this->assertSame(3, $page->lastPage());
        $this->assertCount(2, $page->items());
    }

    abstract protected function repository(): UserRepository;

    private function data(string $email, UserStatus $status = UserStatus::Active): CreateUserData
    {
        return new CreateUserData(
            name: 'Test User',
            email: $email,
            password: 'secret-password',
            status: $status,
        );
    }
}
