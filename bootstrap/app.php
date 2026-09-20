<?php

declare(strict_types=1);

use App\Core\Exceptions\DomainException;
use App\Core\Http\Middleware\RequirePermission;
use App\Core\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => RequirePermission::class,
        ]);

        // "A session that is fully signed in". The application defines what
        // that means at minimum; a module with further requirements appends
        // to this group rather than every route file learning about it.
        $middleware->group('authenticated', [
            'web',
            Authenticate::class.':web',
        ]);

        $middleware->redirectGuestsTo(fn () => Route::has('sign-in') ? route('sign-in') : '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Every use case failure renders through one envelope. Processors throw
        // domain exceptions; nothing below needs a try/catch to shape a response.
        // The framework's own authentication failure gets the same envelope
        // as everything else, so a client parses one error shape.
        $exceptions->render(fn (AuthenticationException $e, $request) => $request->expectsJson()
            ? ApiResponse::failure(
                code: 'core.unauthenticated',
                message: 'Authentication is required.',
                status: 401,
            )
            : null);

        $exceptions->render(function (DomainException $e, Request $request) {
            // Same failure, two deliveries. A browser gets it back on the
            // page it came from; an API client gets the envelope.
            if (! $request->expectsJson()) {
                // Sending a 401 or a 403 "back" would bounce the browser at
                // the page that just refused it. Those land somewhere the
                // caller is actually allowed to be.
                if (in_array($e->statusCode(), [401, 403], true)) {
                    $landing = auth()->check() && Route::has('dashboard')
                        ? route('dashboard')
                        : (Route::has('sign-in') ? route('sign-in') : '/');

                    return redirect()->to($landing)->with('error', $e->getMessage());
                }

                return back()->withInput()->with('error', $e->getMessage());
            }

            return ApiResponse::failure(
                code: $e->errorCode(),
                message: $e->getMessage(),
                details: $e->details(),
                status: $e->statusCode(),
            );
        });
    })->create();
