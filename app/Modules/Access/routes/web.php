<?php

declare(strict_types=1);

use App\Modules\Access\Http\Controllers\Web\CreateRoleFormController;
use App\Modules\Access\Http\Controllers\Web\DeleteRoleFormController;
use App\Modules\Access\Http\Controllers\Web\RoleFormController;
use App\Modules\Access\Http\Controllers\Web\RoleIndexController;
use App\Modules\Access\Http\Controllers\Web\UpdateRoleFormController;
use Illuminate\Support\Facades\Route;

Route::middleware('authenticated')->prefix('roles')->name('roles.')->group(function (): void {
    Route::get('/', RoleIndexController::class)
        ->middleware('permission:access.roles.view')
        ->name('index');

    Route::middleware('permission:access.roles.manage')->group(function (): void {
        Route::get('create', RoleFormController::class)->name('create');
        Route::post('/', CreateRoleFormController::class)->name('store');
        Route::get('{role}/edit', RoleFormController::class)->whereNumber('role')->name('edit');
        Route::put('{role}', UpdateRoleFormController::class)->whereNumber('role')->name('update');
        Route::delete('{role}', DeleteRoleFormController::class)->whereNumber('role')->name('destroy');
    });
});
