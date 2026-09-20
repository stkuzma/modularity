<?php

declare(strict_types=1);

use App\Core\Health\Http\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core API routes
|--------------------------------------------------------------------------
|
| Only framework-level endpoints belong here. Everything else is declared by
| the module that owns it, in app/Modules/<Name>/routes/api.php.
|
*/

Route::get('health', HealthController::class)->name('health');
