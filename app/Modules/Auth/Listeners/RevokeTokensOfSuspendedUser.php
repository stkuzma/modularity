<?php

declare(strict_types=1);

namespace App\Modules\Auth\Listeners;

use App\Core\Audit\AuditTrail;
use App\Modules\Auth\Contracts\TokenRepository;
use App\Modules\Users\Events\UserSuspended;

final readonly class RevokeTokensOfSuspendedUser
{
    public function __construct(
        private TokenRepository $tokens,
        private AuditTrail $audit,
    ) {}

    public function handle(UserSuspended $event): void
    {
        $revoked = $this->tokens->deleteAllForUser($event->userId);

        if ($revoked > 0) {
            $this->audit->record('auth.tokens.revoked_on_suspension', 'user', $event->userId, [
                'count' => $revoked,
            ]);
        }
    }
}
