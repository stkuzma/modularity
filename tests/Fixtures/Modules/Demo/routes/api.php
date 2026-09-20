<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Tests\Fixtures\Modules\Demo\Contracts\Greeter;

Route::get('demo/greet/{name}', fn (Greeter $greeter, string $name): array => [
    'message' => $greeter->greet($name),
])->name('demo.greet');
