<?php

declare(strict_types=1);

use App\Modules\Invitations\Http\Controllers\Web\AcceptInvitationFormController;
use App\Modules\Invitations\Http\Controllers\Web\InvitationScreenController;
use App\Modules\Invitations\Http\Controllers\Web\RevokeInvitationFormController;
use App\Modules\Invitations\Http\Controllers\Web\SendInvitationFormController;
use App\Modules\Invitations\Http\Controllers\Web\ShowAcceptController;
use Illuminate\Support\Facades\Route;

Route::middleware('authenticated')->prefix('invitations')->name('invitations.')->group(function (): void {
    Route::get('/', InvitationScreenController::class)
        ->middleware('permission:invitations.view')
        ->name('index');

    Route::post('/', SendInvitationFormController::class)
        ->middleware('permission:invitations.send')
        ->name('store');

    Route::delete('{invitation}', RevokeInvitationFormController::class)
        ->whereNumber('invitation')
        ->middleware('permission:invitations.send')
        ->name('destroy');
});

// Public: the recipient has no account yet.
Route::get('invite/{token}', ShowAcceptController::class)->name('invitations.accept');
Route::post('invite', AcceptInvitationFormController::class)->name('invitations.accept.submit');
