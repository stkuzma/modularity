<?php

declare(strict_types=1);

namespace App\Core\Health\Console;

use App\Core\Health\HealthResult;
use App\Core\Health\Processors\CheckHealth;
use Illuminate\Console\Command;

final class HealthCheckCommand extends Command
{
    protected $signature = 'health:check {--quiet-on-success : Print nothing when everything is up}';

    protected $description = 'Run the health checks and exit non-zero if any dependency is down';

    public function handle(CheckHealth $processor): int
    {
        $report = $processor->process();

        if ($report->isHealthy() && $this->option('quiet-on-success')) {
            return self::SUCCESS;
        }

        $this->table(
            ['Check', 'Status', 'ms', 'Detail'],
            array_map(
                static fn (HealthResult $result): array => [
                    $result->name,
                    $result->status->value,
                    $result->durationMs,
                    $result->message ?? '',
                ],
                $report->results,
            ),
        );

        if (! $report->isHealthy()) {
            $this->error('Not ready.');

            return self::FAILURE;
        }

        $this->info('Ready.');

        return self::SUCCESS;
    }
}
