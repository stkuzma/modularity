<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\Api\BeginMfaController;
use App\Modules\Auth\Http\Controllers\Api\ConfirmMfaController;
use App\Modules\Auth\Http\Controllers\Api\CurrentIdentityController;
use App\Modules\Auth\Http\Controllers\Api\DisableMfaController;
use App\Modules\Auth\Http\Controllers\Api\IssueTokenController;
use App\Modules\Auth\Http\Controllers\Api\ListTokensController;
use App\Modules\Auth\Http\Controllers\Api\RevokeTokenController;
use App\Modules\Auth\Http\Controllers\Api\SignInController;
use App\Modules\Auth\Http\Controllers\Api\SignOutController;
use App\Modules\Auth\Http\Controllers\Api\VerifySecondFactorController;
use App\Modules\Auth\Http\Controllers\Web\ConfirmSecondFactorFormController;
use App\Modules\Auth\Http\Controllers\Web\CreateTokenFormController;
use App\Modules\Auth\Http\Controllers\Web\DisableSecondFactorFormController;
use App\Modules\Auth\Http\Controllers\Web\EnrolSecondFactorFormController;
use App\Modules\Auth\Http\Controllers\Web\RevokeTokenFormController;
use App\Modules\Auth\Http\Controllers\Web\SecondFactorFormController;
use App\Modules\Auth\Http\Controllers\Web\SecurityController;
use App\Modules\Auth\Http\Controllers\Web\ShowSecondFactorController;
use App\Modules\Auth\Http\Controllers\Web\ShowSignInController;
use App\Modules\Auth\Http\Controllers\Web\SignInFormController;
use App\Modules\Auth\Http\Controllers\Web\SignOutFormController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Session flow
|--------------------------------------------------------------------------
|
| These live on the web stack because they are session bearing: they need a
| session store to hold a pending second-factor challenge, and CSRF because a
| browser sends cookies whether the user meant to or not. They answer JSON
| all the same; "web" here is about the middleware, not the content type.
|
| Minting a token is deliberately one of them. A machine token has to be
| issued to somebody who proved who they are some other way.
|
*/

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('login', SignInController::class)->name('login');
    Route::post('second-factor/verify', VerifySecondFactorController::class)->name('second-factor.verify');

    Route::middleware('auth:web')->group(function (): void {
        Route::post('logout', SignOutController::class)->name('logout');
        Route::get('me', CurrentIdentityController::class)->name('me');

        // Before the challenge: a lost device still needs re-enrolment.
        Route::post('second-factor/enrol', BeginMfaController::class)->name('second-factor.begin');
        Route::post('second-factor/confirm', ConfirmMfaController::class)->name('second-factor.confirm');

        Route::middleware('second-factor')->group(function (): void {
            Route::delete('second-factor', DisableMfaController::class)->name('second-factor.disable');

            Route::get('tokens', ListTokensController::class)->name('tokens.index');
            Route::post('tokens', IssueTokenController::class)->name('tokens.store');
            Route::delete('tokens/{token}', RevokeTokenController::class)
                ->whereNumber('token')
                ->name('tokens.destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Screens
|--------------------------------------------------------------------------
|
| The browser-facing half of this module. Every controller below reuses the
| processors the JSON endpoints use; only the delivery differs.
|
*/

Route::middleware('guest')->group(function (): void {
    Route::get('login', ShowSignInController::class)->name('sign-in');
    Route::post('login', SignInFormController::class)->name('sign-in.submit');
    Route::get('second-factor', ShowSecondFactorController::class)->name('second-factor.show');
    Route::post('second-factor', SecondFactorFormController::class)->name('second-factor.submit');
});

Route::post('logout', SignOutFormController::class)->middleware('auth:web')->name('web.logout');

Route::middleware('authenticated')->prefix('security')->name('security.')->group(function (): void {
    Route::get('/', SecurityController::class)->name('show');

    Route::post('second-factor', EnrolSecondFactorFormController::class)->name('second-factor.enrol');
    Route::post('second-factor/confirm', ConfirmSecondFactorFormController::class)->name('second-factor.confirm');
    Route::delete('second-factor', DisableSecondFactorFormController::class)->name('second-factor.disable');

    Route::post('tokens', CreateTokenFormController::class)->name('tokens.store');
    Route::delete('tokens/{token}', RevokeTokenFormController::class)->whereNumber('token')->name('tokens.destroy');
});
