<?php

declare(strict_types=1);

namespace App\Core\Health;

final readonly class HealthResult
{
    private function __construct(
        public string $name,
        public HealthStatus $status,
        public ?string $message,
        public float $durationMs,
    ) {}

    public static function up(string $name, float $durationMs): self
    {
        return new self($name, HealthStatus::Up, null, $durationMs);
    }

    public static function down(string $name, string $message, float $durationMs): self
    {
        return new self($name, HealthStatus::Down, $message, $durationMs);
    }

    public function isUp(): bool
    {
        return $this->status === HealthStatus::Up;
    }
}
