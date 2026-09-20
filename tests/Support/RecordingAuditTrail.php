<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Core\Audit\AuditTrail;

final class RecordingAuditTrail implements AuditTrail
{
    /** @var list<array{action: string, subjectType: string|null, subjectId: string|int|null, context: array<string, mixed>}> */
    public array $entries = [];

    public function record(
        string $action,
        ?string $subjectType = null,
        string|int|null $subjectId = null,
        array $context = [],
    ): void {
        $this->entries[] = [
            'action' => $action,
            'subjectType' => $subjectType,
            'subjectId' => $subjectId,
            'context' => $context,
        ];
    }
}
