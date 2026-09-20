<?php

declare(strict_types=1);

namespace App\Core\Health\Processors;

use App\Core\Health\HealthCheck;
use App\Core\Health\HealthReport;
use App\Core\Pipeline\Processor;

/**
 * @implements Processor<null, HealthReport>
 */
final readonly class CheckHealth implements Processor
{
    /**
     * @param  iterable<HealthCheck>  $checks
     */
    public function __construct(private iterable $checks) {}

    public function process(mixed $input = null): HealthReport
    {
        $results = [];

        foreach ($this->checks as $check) {
            $results[] = $check->run();
        }

        return new HealthReport($results);
    }
}
