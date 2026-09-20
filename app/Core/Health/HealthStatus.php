<?php

declare(strict_types=1);

namespace App\Core\Health;

enum HealthStatus: string
{
    case Up = 'up';
    case Down = 'down';
}
