<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

use App\Modules\Auth\Models\AuthToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface TokenRepository
{
    public function create(int $userId, string $name, string $tokenHash, ?Carbon $expiresAt): AuthToken;

    public function findByHash(string $tokenHash): ?AuthToken;

    public function findForUser(int $userId, int $tokenId): ?AuthToken;

    /**
     * @return Collection<int, AuthToken>
     */
    public function listForUser(int $userId): Collection;

    public function touch(AuthToken $token): void;

    public function delete(AuthToken $token): void;

    public function deleteAllForUser(int $userId): int;
}
