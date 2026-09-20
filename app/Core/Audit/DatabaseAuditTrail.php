<?php

declare(strict_types=1);

namespace App\Core\Audit;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

final readonly class DatabaseAuditTrail implements AuditTrail
{
    public function __construct(
        private AuthFactory $auth,
        private Request $request,
    ) {}

    public function record(
        string $action,
        ?string $subjectType = null,
        string|int|null $subjectId = null,
        array $context = [],
    ): void {
        $actor = $this->auth->guard()->user();

        AuditLog::query()->create([
            'actor_id' => $actor?->getAuthIdentifier(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId === null ? null : (string) $subjectId,
            'context' => $context,
            'ip_address' => $this->request->ip(),
        ]);
    }
}
