<?php

declare(strict_types=1);

use App\Modules\Users\Http\Controllers\Web\CreateUserFormController;
use App\Modules\Users\Http\Controllers\Web\DeleteUserFormController;
use App\Modules\Users\Http\Controllers\Web\UpdateUserFormController;
use App\Modules\Users\Http\Controllers\Web\UserFormController;
use App\Modules\Users\Http\Controllers\Web\UserIndexController;
use Illuminate\Support\Facades\Route;

Route::middleware('authenticated')->prefix('users')->name('users.')->group(function (): void {
    Route::get('/', UserIndexController::class)
        ->middleware('permission:users.view')
        ->name('index');

    Route::get('create', UserFormController::class)
        ->middleware('permission:users.create')
        ->name('create');

    Route::post('/', CreateUserFormController::class)
        ->middleware('permission:users.create')
        ->name('store');

    Route::get('{user}/edit', UserFormController::class)
        ->whereNumber('user')
        ->middleware('permission:users.update')
        ->name('edit');

    Route::put('{user}', UpdateUserFormController::class)
        ->whereNumber('user')
        ->middleware('permission:users.update')
        ->name('update');

    Route::delete('{user}', DeleteUserFormController::class)
        ->whereNumber('user')
        ->middleware('permission:users.delete')
        ->name('destroy');
});
