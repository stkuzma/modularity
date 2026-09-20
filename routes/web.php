<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application shell
|--------------------------------------------------------------------------
|
| Only the frame lives here. Every screen that belongs to a domain is declared
| by the module that owns it, and the shell keeps working when one is removed.
|
*/

Route::get('/', HomeController::class)->name('home');

Route::middleware('authenticated')->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});
