<?php

declare(strict_types=1);

namespace App\Core\Module;

use App\Core\Access\PermissionCatalogue;
use App\Core\Navigation\Navigation;
use App\Core\Navigation\NavigationItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * The contract a module plugs into. Routes, migrations, views and config are
 * discovered from its directory; everything else is declared here.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    protected string $apiMiddleware = 'api';

    protected string $webMiddleware = 'web';

    protected string $apiPrefix = 'api';

    final public function register(): void
    {
        foreach ($this->bindings() as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }

        $this->mergeModuleConfig();
        $this->declarePermissions();
        $this->onRegister();
    }

    final public function boot(): void
    {
        $base = $this->modulePath();

        $this->loadModuleMigrations($base);
        $this->loadModuleViews($base);
        $this->loadModuleRoutes($base);

        $navigation = $this->navigation();

        if ($navigation !== []) {
            // On boot, not register: the router has to exist first.
            $this->app->make(Navigation::class)->add($navigation);
        }

        foreach ($this->policies() as $model => $policy) {
            Gate::policy($model, $policy);
        }

        $this->onBoot();
    }

    final public function moduleName(): string
    {
        $short = (new ReflectionClass(static::class))->getShortName();

        return str_replace('ServiceProvider', '', $short);
    }

    final public function modulePath(): string
    {
        $file = (new ReflectionClass(static::class))->getFileName();

        if ($file === false) {
            throw new ModuleException(static::class.' could not be located on disk.');
        }

        return dirname($file, 2);
    }

    /**
     * @return array<class-string, class-string>
     */
    abstract protected function bindings(): array;

    /**
     * @return array<class-string, class-string>
     */
    protected function policies(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    protected function permissions(): array
    {
        return [];
    }

    /**
     * @return list<NavigationItem>
     */
    protected function navigation(): array
    {
        return [];
    }

    protected function onRegister(): void
    {
        //
    }

    protected function onBoot(): void
    {
        //
    }

    private function declarePermissions(): void
    {
        $permissions = $this->permissions();

        if ($permissions === []) {
            return;
        }

        $this->app->make(PermissionCatalogue::class)->declare($this->moduleName(), $permissions);
    }

    private function mergeModuleConfig(): void
    {
        $path = $this->modulePath().'/config/'.strtolower($this->moduleName()).'.php';

        if (is_file($path)) {
            $this->mergeConfigFrom($path, strtolower($this->moduleName()));
        }
    }

    private function loadModuleMigrations(string $base): void
    {
        $path = $base.'/Database/Migrations';

        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    private function loadModuleViews(string $base): void
    {
        $path = $base.'/Resources/views';

        if (is_dir($path)) {
            $this->loadViewsFrom($path, strtolower($this->moduleName()));
        }
    }

    private function loadModuleRoutes(string $base): void
    {
        $api = $base.'/routes/api.php';

        if (is_file($api)) {
            Route::middleware($this->apiMiddleware)
                ->prefix($this->apiPrefix)
                ->group($api);
        }

        $web = $base.'/routes/web.php';

        if (is_file($web)) {
            Route::middleware($this->webMiddleware)->group($web);
        }

        // Routes added after boot need the lookup tables refreshed for route().
        if ($this->app->isBooted()) {
            Route::getRoutes()->refreshNameLookups();
            Route::getRoutes()->refreshActionLookups();
        }
    }
}
