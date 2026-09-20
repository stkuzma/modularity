<?php

declare(strict_types=1);

namespace Tests\Fixtures\Modules\Demo\Services;

use Tests\Fixtures\Modules\Demo\Contracts\Greeter;

final class PlainGreeter implements Greeter
{
    public function greet(string $name): string
    {
        return "Hello, {$name}.";
    }
}
