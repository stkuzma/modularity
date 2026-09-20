<?php

declare(strict_types=1);

// Fails the build when line coverage drops below the floor.
// Usage: php scripts/coverage-gate.php coverage.xml 75

$file = $argv[1] ?? 'coverage.xml';
$floor = (float) ($argv[2] ?? 75);

if (! is_file($file)) {
    fwrite(STDERR, "No coverage report at {$file}.\n");
    exit(1);
}

$metrics = simplexml_load_file($file)?->project?->metrics;

if ($metrics === null) {
    fwrite(STDERR, "Could not read metrics from {$file}.\n");
    exit(1);
}

$total = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];
$percent = $total === 0 ? 0.0 : round($covered / $total * 100, 2);

printf("line coverage: %.2f%% (%d/%d), floor %.0f%%\n", $percent, $covered, $total, $floor);

exit($percent >= $floor ? 0 : 1);
