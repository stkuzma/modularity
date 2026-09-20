<?php

declare(strict_types=1);

namespace App\Core\Health\Http;

use App\Core\Health\Presenters\HealthPresenter;
use App\Core\Health\Processors\CheckHealth;
use App\Core\Http\ApiController;
use App\Core\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class HealthController extends ApiController
{
    public function __construct(
        private readonly CheckHealth $processor,
        private readonly HealthPresenter $presenter,
    ) {}

    public function __invoke(): JsonResponse
    {
        $report = $this->processor->process();

        return ApiResponse::success(
            $this->presenter->present($report),
            [],
            $report->isHealthy() ? 200 : 503,
        );
    }
}
