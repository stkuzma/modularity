<?php

declare(strict_types=1);

namespace App\Core\Audit;

/** Append-only record of what people did. Written from processors, not model events. */
interface AuditTrail
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $action,
        ?string $subjectType = null,
        string|int|null $subjectId = null,
        array $context = [],
    ): void;
}
