<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Support;

use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Data\UserFilter;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<int, User> */
    private array $users = [];

    private int $nextId = 1;

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    public function existsByEmail(string $email, ?int $ignoreId = null): bool
    {
        foreach ($this->users as $user) {
            if ($user->email === $email && $user->id !== $ignoreId) {
                return true;
            }
        }

        return false;
    }

    public function create(CreateUserData $data): User
    {
        $user = new User;

        // Raw attributes: the password cast reaches for the Hash facade.
        $user->setRawAttributes([
            'id' => $this->nextId++,
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'status' => $data->status->value,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ], true);

        $user->exists = true;

        return $this->users[$user->id] = $user;
    }

    public function update(User $user, UpdateUserData $data): User
    {
        $changes = $data->changes();

        if (isset($changes['status'])) {
            $changes['status'] = $data->status?->value;
        }

        $user->setRawAttributes(array_merge($user->getAttributes(), $changes), true);

        return $this->users[$user->id] = $user;
    }

    public function delete(User $user): void
    {
        unset($this->users[$user->id]);
    }

    public function paginate(UserFilter $filter): LengthAwarePaginatorContract
    {
        $matching = (new Collection($this->users))
            ->when(
                $filter->search !== null,
                fn (Collection $users): Collection => $users->filter(
                    fn (User $user): bool => str_contains($user->name, (string) $filter->search)
                        || str_contains($user->email, (string) $filter->search),
                ),
            )
            ->when(
                $filter->status !== null,
                fn (Collection $users): Collection => $users->filter(
                    fn (User $user): bool => $user->status === $filter->status,
                ),
            )
            ->sortByDesc('id')
            ->values();

        return new LengthAwarePaginator(
            items: $matching->forPage($filter->page, $filter->perPage)->values(),
            total: $matching->count(),
            perPage: $filter->perPage,
            currentPage: $filter->page,
        );
    }
}
