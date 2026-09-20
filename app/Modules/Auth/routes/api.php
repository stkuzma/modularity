<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\Api\CurrentIdentityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bearer token flow
|--------------------------------------------------------------------------
|
| Stateless. The module-token guard is registered by this module's provider,
| along with the auth.guards.api entry that names it, so removing the module
| takes the driver and its configuration away together.
|
*/

Route::prefix('auth')->name('auth.api.')->middleware('auth:api')->group(function (): void {
    Route::get('me', CurrentIdentityController::class)->name('me');
});
