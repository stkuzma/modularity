<?php

declare(strict_types=1);

namespace App\Modules\Users\Repositories;

use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Data\UserFilter;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentUserRepository implements UserRepository
{
    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function existsByEmail(string $email, ?int $ignoreId = null): bool
    {
        return User::query()
            ->where('email', $email)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function create(CreateUserData $data): User
    {
        $user = new User;

        $user->name = $data->name;
        $user->email = $data->email;
        $user->password = $data->password;
        $user->status = $data->status;
        $user->save();

        return $user;
    }

    public function update(User $user, UpdateUserData $data): User
    {
        $user->fill($data->changes());
        $user->save();

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function paginate(UserFilter $filter): LengthAwarePaginator
    {
        return User::query()
            ->when(
                $filter->search !== null,
                fn (Builder $query): Builder => $query->where(
                    fn (Builder $inner): Builder => $inner
                        ->where('name', 'like', '%'.$filter->search.'%')
                        ->orWhere('email', 'like', '%'.$filter->search.'%'),
                ),
            )
            ->when(
                $filter->status !== null,
                fn (Builder $query): Builder => $query->where('status', $filter->status?->value),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(perPage: $filter->perPage, page: $filter->page);
    }
}
