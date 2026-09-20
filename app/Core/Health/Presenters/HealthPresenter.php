<?php

declare(strict_types=1);

namespace App\Core\Health\Presenters;

use App\Core\Health\HealthReport;
use App\Core\Health\HealthResult;
use App\Core\Pipeline\Presenter;
use Illuminate\Contracts\Config\Repository;

/**
 * @implements Presenter<HealthReport>
 */
final readonly class HealthPresenter implements Presenter
{
    public function __construct(private Repository $config) {}

    public function present(mixed $subject): array
    {
        return [
            'status' => $subject->status()->value,
            'colour' => $this->config->get('app.colour'),
            'checks' => array_map(
                static fn (HealthResult $result): array => [
                    'name' => $result->name,
                    'status' => $result->status->value,
                    'duration_ms' => $result->durationMs,
                    'message' => $result->message,
                ],
                $subject->results,
            ),
        ];
    }
}
