<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Contracts\TokenRepository;
use App\Modules\Auth\Models\AuthToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class EloquentTokenRepository implements TokenRepository
{
    public function create(int $userId, string $name, string $tokenHash, ?Carbon $expiresAt): AuthToken
    {
        $token = new AuthToken;

        $token->user_id = $userId;
        $token->name = $name;
        $token->token_hash = $tokenHash;
        $token->expires_at = $expiresAt;
        $token->save();

        return $token;
    }

    public function findByHash(string $tokenHash): ?AuthToken
    {
        return AuthToken::query()->where('token_hash', $tokenHash)->first();
    }

    public function findForUser(int $userId, int $tokenId): ?AuthToken
    {
        return AuthToken::query()->where('user_id', $userId)->whereKey($tokenId)->first();
    }

    public function listForUser(int $userId): Collection
    {
        return AuthToken::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->toBase();
    }

    public function touch(AuthToken $token): void
    {
        $token->forceFill(['last_used_at' => Carbon::now()])->saveQuietly();
    }

    public function delete(AuthToken $token): void
    {
        $token->delete();
    }

    public function deleteAllForUser(int $userId): int
    {
        $deleted = AuthToken::query()->where('user_id', $userId)->delete();

        return is_int($deleted) ? $deleted : 0;
    }
}
