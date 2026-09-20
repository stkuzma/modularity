<?php

declare(strict_types=1);

namespace App\Modules\Users\Contracts;

use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Data\UserFilter;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepository
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function existsByEmail(string $email, ?int $ignoreId = null): bool;

    public function create(CreateUserData $data): User;

    public function update(User $user, UpdateUserData $data): User;

    public function delete(User $user): void;

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(UserFilter $filter): LengthAwarePaginator;
}
