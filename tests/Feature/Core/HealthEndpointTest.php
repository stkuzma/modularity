<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use App\Core\Health\HealthCheck;
use App\Core\Health\HealthReport;
use App\Core\Health\HealthResult;
use App\Core\Health\Processors\CheckHealth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    #[Test]
    public function it_reports_up_when_every_dependency_answers(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('data.status', 'up')
            ->assertJsonPath('data.colour', null)
            ->assertJsonPath('data.checks.0.name', 'database')
            ->assertJsonPath('data.checks.1.name', 'cache');
    }

    #[Test]
    public function it_returns_503_when_a_dependency_is_down(): void
    {
        $this->app->bind(CheckHealth::class, fn (): CheckHealth => new CheckHealth([
            new class implements HealthCheck
            {
                public function name(): string
                {
                    return 'database';
                }

                public function run(): HealthResult
                {
                    return HealthResult::down($this->name(), 'Connection refused.', 1.0);
                }
            },
        ]));

        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJsonPath('data.status', 'down')
            ->assertJsonPath('data.checks.0.message', 'Connection refused.');
    }

    #[Test]
    public function it_reports_which_half_of_a_blue_green_pair_answered(): void
    {
        config(['app.colour' => 'green']);

        $this->getJson('/api/health')->assertOk()->assertJsonPath('data.colour', 'green');
    }

    #[Test]
    public function a_report_is_healthy_only_when_every_result_is_up(): void
    {
        $up = HealthResult::up('a', 1.0);
        $down = HealthResult::down('b', 'nope', 1.0);

        $this->assertTrue((new HealthReport([$up]))->isHealthy());
        $this->assertFalse((new HealthReport([$up, $down]))->isHealthy());
        $this->assertTrue((new HealthReport([]))->isHealthy());
    }
}
