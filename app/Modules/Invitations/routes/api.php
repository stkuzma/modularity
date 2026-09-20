<?php

declare(strict_types=1);

use App\Modules\Invitations\Http\Controllers\Api\ListInvitationsController;
use App\Modules\Invitations\Http\Controllers\Api\RevokeInvitationController;
use App\Modules\Invitations\Http\Controllers\Api\SendInvitationController;
use Illuminate\Support\Facades\Route;

Route::prefix('invitations')->name('api.invitations.')->group(function (): void {
    Route::get('/', ListInvitationsController::class)
        ->middleware('permission:invitations.view')
        ->name('index');

    Route::post('/', SendInvitationController::class)
        ->middleware('permission:invitations.send')
        ->name('store');

    Route::delete('{invitation}', RevokeInvitationController::class)
        ->whereNumber('invitation')
        ->middleware('permission:invitations.send')
        ->name('destroy');
});
