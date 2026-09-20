<?php

declare(strict_types=1);

namespace App\Core\Health\Checks;

use App\Core\Health\HealthCheck;
use App\Core\Health\HealthResult;
use Illuminate\Contracts\Cache\Repository;
use Throwable;

final readonly class CacheCheck implements HealthCheck
{
    private const PROBE_KEY = 'health:probe';

    public function __construct(private Repository $cache) {}

    public function name(): string
    {
        return 'cache';
    }

    public function run(): HealthResult
    {
        $started = microtime(true);

        try {
            $this->cache->put(self::PROBE_KEY, 'ok', 5);

            if ($this->cache->get(self::PROBE_KEY) !== 'ok') {
                return HealthResult::down($this->name(), 'Cache did not return what it stored.', $this->elapsed($started));
            }
        } catch (Throwable $e) {
            return HealthResult::down($this->name(), $e->getMessage(), $this->elapsed($started));
        }

        return HealthResult::up($this->name(), $this->elapsed($started));
    }

    private function elapsed(float $started): float
    {
        return round((microtime(true) - $started) * 1000, 2);
    }
}
