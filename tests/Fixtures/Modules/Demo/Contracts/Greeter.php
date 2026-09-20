<?php

declare(strict_types=1);

namespace Tests\Fixtures\Modules\Demo\Contracts;

interface Greeter
{
    public function greet(string $name): string;
}
