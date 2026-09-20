<?php

declare(strict_types=1);

namespace App\Core\Health\Checks;

use App\Core\Health\HealthCheck;
use App\Core\Health\HealthResult;
use Illuminate\Database\DatabaseManager;
use Throwable;

final readonly class DatabaseCheck implements HealthCheck
{
    public function __construct(private DatabaseManager $database) {}

    public function name(): string
    {
        return 'database';
    }

    public function run(): HealthResult
    {
        $started = microtime(true);

        try {
            $this->database->connection()->select('select 1');
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
