<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Access\AccessChecker;
use App\Core\Access\DeniesEverything;
use App\Core\Access\PermissionCatalogue;
use App\Core\Audit\AuditTrail;
use App\Core\Audit\DatabaseAuditTrail;
use App\Core\Auth\AllowsAnyAccount;
use App\Core\Auth\SignInGuard;
use App\Core\Health\Checks\CacheCheck;
use App\Core\Health\Checks\DatabaseCheck;
use App\Core\Health\Console\HealthCheckCommand;
use App\Core\Health\Processors\CheckHealth;
use App\Core\Module\ModuleRegistry;
use App\Core\Module\ModuleServiceProvider;
use App\Core\Navigation\Navigation;
use App\Core\Navigation\NavigationItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance(PermissionCatalogue::class, new PermissionCatalogue);
        $this->app->singleton(AuditTrail::class, DatabaseAuditTrail::class);
        $this->app->singleton(AccessChecker::class, DeniesEverything::class);
        $this->app->singleton(SignInGuard::class, AllowsAnyAccount::class);
        $this->app->singleton(Navigation::class);

        $this->registerHealthChecks();

        // Modules declare permissions on register(), so the catalogue goes first.
        $this->registerModules();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([HealthCheckCommand::class]);
        }

        // The shell's own entry. Everything else is contributed by a module.
        $this->app->make(Navigation::class)->add([
            new NavigationItem(label: 'Dashboard', route: 'dashboard', order: 0),
        ]);

        $catalogue = $this->app->make(PermissionCatalogue::class);

        foreach ($catalogue->names() as $permission) {
            Gate::define(
                $permission,
                fn (Authenticatable $actor): bool => $this->app->make(AccessChecker::class)
                    ->allows($actor, $permission),
            );
        }
    }

    private function registerHealthChecks(): void
    {
        $this->app->singleton(DatabaseCheck::class);
        $this->app->singleton(CacheCheck::class);

        $this->app->tag([DatabaseCheck::class, CacheCheck::class], 'health.checks');

        $this->app->singleton(
            CheckHealth::class,
            static fn (Container $app): CheckHealth => new CheckHealth($app->tagged('health.checks')),
        );
    }

    private function registerModules(): void
    {
        /** @var list<class-string<ModuleServiceProvider>> $enabled */
        $enabled = array_values((array) config('modules.enabled', []));

        $registry = new ModuleRegistry($enabled);

        $this->app->instance(ModuleRegistry::class, $registry);

        $registry->registerInto($this->app);
    }
}
