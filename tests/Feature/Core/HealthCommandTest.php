<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use App\Core\Health\HealthCheck;
use App\Core\Health\HealthResult;
use App\Core\Health\Processors\CheckHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HealthCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_exits_zero_when_every_dependency_answers(): void
    {
        $this->command('health:check')->assertExitCode(0);
    }

    #[Test]
    public function it_exits_non_zero_when_a_dependency_is_down(): void
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

        $this->command('health:check')->assertExitCode(1);
    }

    #[Test]
    public function it_can_stay_quiet_when_everything_is_up(): void
    {
        $this->command('health:check', ['--quiet-on-success' => true])
            ->doesntExpectOutput('Ready.')
            ->assertExitCode(0);
    }
}
