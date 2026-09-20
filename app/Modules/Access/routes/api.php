<?php

declare(strict_types=1);

use App\Modules\Access\Http\Controllers\Api\AssignRoleController;
use App\Modules\Access\Http\Controllers\Api\CreateRoleController;
use App\Modules\Access\Http\Controllers\Api\DeleteRoleController;
use App\Modules\Access\Http\Controllers\Api\ListPermissionsController;
use App\Modules\Access\Http\Controllers\Api\ListRolesController;
use App\Modules\Access\Http\Controllers\Api\RevokeRoleController;
use App\Modules\Access\Http\Controllers\Api\SyncRolePermissionsController;
use App\Modules\Access\Http\Controllers\Api\UpdateRoleController;
use Illuminate\Support\Facades\Route;

Route::get('permissions', ListPermissionsController::class)
    ->middleware('permission:access.roles.view')
    ->name('api.permissions.index');

Route::prefix('roles')->name('api.roles.')->group(function (): void {
    Route::get('/', ListRolesController::class)
        ->middleware('permission:access.roles.view')
        ->name('index');

    Route::post('/', CreateRoleController::class)
        ->middleware('permission:access.roles.manage')
        ->name('store');

    Route::patch('{role}', UpdateRoleController::class)
        ->whereNumber('role')
        ->middleware('permission:access.roles.manage')
        ->name('update');

    Route::delete('{role}', DeleteRoleController::class)
        ->whereNumber('role')
        ->middleware('permission:access.roles.manage')
        ->name('destroy');

    Route::put('{role}/permissions', SyncRolePermissionsController::class)
        ->whereNumber('role')
        ->middleware('permission:access.roles.manage')
        ->name('permissions.sync');

    Route::post('{role}/users', AssignRoleController::class)
        ->whereNumber('role')
        ->middleware('permission:access.assign')
        ->name('users.attach');

    Route::delete('{role}/users', RevokeRoleController::class)
        ->whereNumber('role')
        ->middleware('permission:access.assign')
        ->name('users.detach');
});
