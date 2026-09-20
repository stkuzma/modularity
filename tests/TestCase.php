<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Testing\PendingCommand;
use Illuminate\Testing\TestResponse;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    protected function command(string $command, array $parameters = []): PendingCommand
    {
        $pending = $this->artisan($command, $parameters);

        if (! $pending instanceof PendingCommand) {
            throw new RuntimeException("[{$command}] did not return a pending command.");
        }

        return $pending;
    }

    /**
     * @param  TestResponse<JsonResponse>  $response
     */
    protected function jsonString(TestResponse $response, string $key): string
    {
        $value = $response->json($key);

        $this->assertIsString($value, "[{$key}] was expected to be a string.");

        return $value;
    }

    /**
     * @param  TestResponse<JsonResponse>  $response
     * @return list<mixed>
     */
    protected function jsonList(TestResponse $response, string $key): array
    {
        $value = $response->json($key);

        $this->assertIsArray($value, "[{$key}] was expected to be a list.");

        return array_values($value);
    }
}
