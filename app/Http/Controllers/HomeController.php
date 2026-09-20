<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

final class HomeController
{
    public function __invoke(): RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return Route::has('sign-in')
            ? redirect()->route('sign-in')
            : redirect()->route('dashboard');
    }
}
