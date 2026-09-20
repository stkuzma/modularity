<?php

declare(strict_types=1);

use App\Modules\Users\Http\Controllers\Api\CreateUserController;
use App\Modules\Users\Http\Controllers\Api\DeleteUserController;
use App\Modules\Users\Http\Controllers\Api\ListUsersController;
use App\Modules\Users\Http\Controllers\Api\ShowUserController;
use App\Modules\Users\Http\Controllers\Api\UpdateUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Users module API
|--------------------------------------------------------------------------
|
| One invokable controller per endpoint, so the file reads as a table of
| contents and each controller injects only what its own route needs.
|
| Route names are namespaced by channel: the screens in routes/web.php own
| "users.*", so the JSON endpoints own "api.users.*". Two deliveries of the
| same use case need two names.
|
*/

Route::prefix('users')->name('api.users.')->group(function (): void {
    Route::get('/', ListUsersController::class)
        ->middleware('permission:users.view')
        ->name('index');

    Route::post('/', CreateUserController::class)
        ->middleware('permission:users.create')
        ->name('store');

    Route::get('{user}', ShowUserController::class)
        ->whereNumber('user')
        ->middleware('permission:users.view')
        ->name('show');

    Route::patch('{user}', UpdateUserController::class)
        ->whereNumber('user')
        ->middleware('permission:users.update')
        ->name('update');

    Route::delete('{user}', DeleteUserController::class)
        ->whereNumber('user')
        ->middleware('permission:users.delete')
        ->name('destroy');
});
