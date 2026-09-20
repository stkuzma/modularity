<?php

declare(strict_types=1);

namespace App\Core\Module;

use Illuminate\Contracts\Foundation\Application;
use ReflectionClass;

final class ModuleRegistry
{
    /** @var list<class-string<ModuleServiceProvider>> */
    private array $providers;

    /**
     * @param  array<array-key, class-string<ModuleServiceProvider>>  $providers
     */
    public function __construct(array $providers)
    {
        foreach ($providers as $provider) {
            if (! is_subclass_of($provider, ModuleServiceProvider::class)) {
                throw new ModuleException(
                    "[{$provider}] is listed in config/modules.php but does not extend "
                    .ModuleServiceProvider::class.'.'
                );
            }
        }

        $this->providers = array_values($providers);
    }

    /**
     * @return list<class-string<ModuleServiceProvider>>
     */
    public function providers(): array
    {
        return $this->providers;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_map(
            static fn (string $provider): string => str_replace(
                'ServiceProvider',
                '',
                (new ReflectionClass($provider))->getShortName(),
            ),
            $this->providers,
        );
    }

    public function registerInto(Application $app): void
    {
        foreach ($this->providers as $provider) {
            $app->register($provider);
        }
    }
}
