<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use App\Core\Module\ModuleServiceProvider;
use App\Core\Navigation\NavigationItem;
use App\Modules\Auth\Contracts\MfaRepository;
use App\Modules\Auth\Contracts\SecondFactor;
use App\Modules\Auth\Contracts\TokenRepository;
use App\Modules\Auth\Http\Middleware\RequireSecondFactor;
use App\Modules\Auth\Listeners\RevokeTokensOfSuspendedUser;
use App\Modules\Auth\Repositories\EloquentMfaRepository;
use App\Modules\Auth\Repositories\EloquentTokenRepository;
use App\Modules\Auth\Services\TokenMint;
use App\Modules\Auth\Services\Totp;
use App\Modules\Users\Events\UserSuspended;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use RuntimeException;

final class AuthServiceProvider extends ModuleServiceProvider
{
    protected function bindings(): array
    {
        return [
            TokenRepository::class => EloquentTokenRepository::class,
            MfaRepository::class => EloquentMfaRepository::class,
            SecondFactor::class => Totp::class,
        ];
    }

    protected function navigation(): array
    {
        return [
            new NavigationItem(label: 'Security', route: 'security.show', order: 90),
        ];
    }

    protected function onRegister(): void
    {
        // Contracts rather than the Auth facade, so processors stay unit testable.
        $this->app->bind(UserProvider::class, static fn (): UserProvider => Auth::createUserProvider('users')
            ?? throw new RuntimeException('The [users] auth provider is not configured.'));

        // The return type is the assertion: a non-stateful web guard fails here.
        $this->app->bind(StatefulGuard::class, static fn (): StatefulGuard => Auth::guard('web'));
    }

    protected function onBoot(): void
    {
        $this->registerTokenGuard();

        Event::listen(UserSuspended::class, RevokeTokensOfSuspendedUser::class);

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('second-factor', RequireSecondFactor::class);

        // Signed in now also means the challenge was answered.
        $router->pushMiddlewareToGroup('authenticated', RequireSecondFactor::class);
    }

    private function registerTokenGuard(): void
    {
        Auth::viaRequest('module-token', function (Request $request): mixed {
            $plainText = $request->bearerToken();

            if ($plainText === null || $plainText === '') {
                return null;
            }

            $tokens = $this->app->make(TokenRepository::class);
            $mint = $this->app->make(TokenMint::class);

            $token = $tokens->findByHash($mint->hash($plainText));

            if ($token === null || $token->hasExpired()) {
                return null;
            }

            $tokens->touch($token);

            return Auth::createUserProvider('users')?->retrieveById($token->user_id);
        });

        config()->set('auth.guards.api', [
            'driver' => 'module-token',
            'provider' => 'users',
        ]);
    }
}
