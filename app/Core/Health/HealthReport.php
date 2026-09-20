<?php

declare(strict_types=1);

namespace App\Core\Health;

final readonly class HealthReport
{
    /**
     * @param  list<HealthResult>  $results
     */
    public function __construct(public array $results) {}

    public function isHealthy(): bool
    {
        foreach ($this->results as $result) {
            if (! $result->isUp()) {
                return false;
            }
        }

        return true;
    }

    public function status(): HealthStatus
    {
        return $this->isHealthy() ? HealthStatus::Up : HealthStatus::Down;
    }
}
