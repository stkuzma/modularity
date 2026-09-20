<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use App\Core\Module\ModuleRegistry;
use App\Core\Module\ModuleServiceProvider;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use SplFileInfo;
use Tests\TestCase;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function the_core_never_references_a_module(): void
    {
        foreach ($this->phpFilesIn(app_path('Core')) as $file) {
            $this->assertStringNotContainsString(
                'App\\Modules\\',
                (string) file_get_contents($file),
                basename($file).' references a module. app/Core must stay module agnostic.',
            );
        }
    }

    #[Test]
    public function processors_do_not_know_about_http(): void
    {
        foreach ($this->phpFilesIn(app_path()) as $file) {
            if (! str_contains($file, DIRECTORY_SEPARATOR.'Processors'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $source = (string) file_get_contents($file);

            $this->assertStringNotContainsString(
                'use Illuminate\\Http\\',
                $source,
                basename($file).' imports Illuminate\\Http. A processor is a use case, not a controller.',
            );
            $this->assertStringNotContainsString(
                'use Illuminate\\Support\\Facades\\Request',
                $source,
                basename($file).' reaches for the request. Pass a DTO in instead.',
            );
        }
    }

    #[Test]
    public function a_module_only_reaches_another_module_through_its_contracts_or_data(): void
    {
        $allowed = ['Contracts', 'Data', 'Enums', 'Events'];
        $violations = [];

        foreach ($this->moduleDirectories() as $module => $directory) {
            foreach ($this->phpFilesIn($directory) as $file) {
                // Production code only; integration tests may compose modules.
                if (str_contains($file, DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR)) {
                    continue;
                }

                // The whole file, not just its use statements: an inline
                // \App\Modules\Other\Models\Thing, an app() call or a
                // class name in a string all reach across the boundary too.
                preg_match_all(
                    '/App\\\\+Modules\\\\+([A-Za-z0-9_]+)\\\\+([A-Za-z0-9_]+)/',
                    (string) file_get_contents($file),
                    $matches,
                    PREG_SET_ORDER,
                );

                foreach ($matches as [, $target, $segment]) {
                    if ($target === $module || in_array($segment, $allowed, true)) {
                        continue;
                    }

                    $violations[] = sprintf(
                        '%s reaches into %s\\%s',
                        basename($file),
                        $target,
                        $segment,
                    );
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            'Cross-module access goes through Contracts, Data, Enums or Events only.',
        );
    }

    #[Test]
    public function a_module_does_not_export_its_persistence_shape(): void
    {
        $violations = [];

        foreach ($this->moduleDirectories() as $module => $directory) {
            foreach ($this->phpFilesIn($directory) as $file) {
                if (str_contains($file, DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR)) {
                    continue;
                }

                preg_match_all(
                    '/^use\s+App\\\\Modules\\\\([A-Za-z0-9_]+)\\\\Contracts\\\\([A-Za-z0-9_]+)/m',
                    (string) file_get_contents($file),
                    $matches,
                    PREG_SET_ORDER,
                );

                foreach ($matches as [, $target, $contract]) {
                    if ($target !== $module && str_ends_with($contract, 'Repository')) {
                        $violations[] = sprintf('%s depends on %s\\%s', basename($file), $target, $contract);
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            'A module offers other modules capabilities, not the shape of its storage. '
            .'Repository contracts stay internal.',
        );
    }

    #[Test]
    public function a_module_does_not_query_another_modules_tables(): void
    {
        $owner = [];

        foreach ($this->moduleDirectories() as $module => $directory) {
            foreach ($this->phpFilesIn($directory.'/Database/Migrations') as $migration) {
                preg_match_all(
                    "/Schema::create\\(\\s*'([a-z0-9_]+)'/",
                    (string) file_get_contents($migration),
                    $tables,
                );

                foreach ($tables[1] as $table) {
                    $owner[$table] = $module;
                }
            }
        }

        $violations = [];

        foreach ($this->moduleDirectories() as $module => $directory) {
            foreach ($this->phpFilesIn($directory) as $file) {
                if (str_contains($file, DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR)
                    || str_contains($file, DIRECTORY_SEPARATOR.'Migrations'.DIRECTORY_SEPARATOR)) {
                    continue;
                }

                preg_match_all(
                    "/(?:->|::)(?:table|from)\\(\\s*'([a-z0-9_]+)'/",
                    (string) file_get_contents($file),
                    $queried,
                );

                foreach ($queried[1] as $table) {
                    if (($owner[$table] ?? $module) !== $module) {
                        $violations[] = sprintf('%s queries %s, owned by %s', basename($file), $table, $owner[$table]);
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            'A table belongs to the module whose migration created it. Reaching it with a raw '
            .'query is the same coupling as importing the model, with none of the visibility.',
        );
    }

    #[Test]
    public function a_module_only_binds_contracts_it_owns(): void
    {
        $violations = [];

        foreach ($this->app->make(ModuleRegistry::class)->providers() as $provider) {
            $module = str_replace('ServiceProvider', '', (new ReflectionClass($provider))->getShortName());

            $bindings = (new ReflectionMethod($provider, 'bindings'));
            $bindings->setAccessible(true);

            /** @var array<class-string, class-string> $declared */
            $declared = $bindings->invoke(new $provider($this->app));

            $bound = array_keys($declared);

            // bindings() is the declared map; onRegister() and onBoot() can
            // call the container directly, so the provider's source is read
            // for those too.
            $file = (string) (new ReflectionClass($provider))->getFileName();
            $source = (string) file_get_contents($file);

            preg_match_all('/^use\s+([A-Za-z0-9_\\\\]+);/m', $source, $uses);
            $aliases = [];

            foreach ($uses[1] as $imported) {
                $aliases[substr((string) strrchr('\\'.$imported, '\\'), 1)] = $imported;
            }

            preg_match_all(
                '/->(?:bind|singleton|instance|bindIf|singletonIf|extend)\(\s*\\\\?([A-Za-z0-9_\\\\]+)::class/',
                $source,
                $calls,
            );

            foreach ($calls[1] as $named) {
                $bound[] = str_contains($named, '\\') ? ltrim($named, '\\') : ($aliases[$named] ?? $named);
            }

            foreach (array_unique($bound) as $abstract) {
                if (! str_starts_with($abstract, 'App\\Modules\\')) {
                    continue;
                }

                if (! str_starts_with($abstract, "App\\Modules\\{$module}\\")) {
                    $violations[] = "{$module} binds {$abstract}";
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            'A module may implement a core contract, but redefining another module\'s contract '
            .'silently changes what that contract means for the whole application.',
        );
    }

    #[Test]
    public function module_dependencies_form_no_cycle(): void
    {
        $edges = [];

        foreach ($this->moduleDirectories() as $module => $directory) {
            foreach ($this->phpFilesIn($directory) as $file) {
                if (str_contains($file, DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR)) {
                    continue;
                }

                preg_match_all(
                    '/App\\\\+Modules\\\\+([A-Za-z0-9_]+)/',
                    (string) file_get_contents($file),
                    $matches,
                );

                foreach ($matches[1] as $target) {
                    if ($target !== $module) {
                        $edges[$module][$target] = true;
                    }
                }
            }
        }

        $cycle = $this->firstCycle($edges);

        $this->assertNull(
            $cycle,
            'Modules depend on each other in a cycle: '.implode(' -> ', (array) $cycle)
            .'. A cycle means neither module can be removed and the dependency graph says nothing.',
        );
    }

    #[Test]
    public function every_module_on_disk_is_registered_and_every_registered_module_exists(): void
    {
        $registered = $this->app->make(ModuleRegistry::class)->names();
        $onDisk = array_keys($this->moduleDirectories());

        sort($registered);
        sort($onDisk);

        $this->assertSame(
            $onDisk,
            $registered,
            'config/modules.php and app/Modules disagree about which modules exist.',
        );
    }

    #[Test]
    public function every_registered_provider_extends_the_module_contract(): void
    {
        $providers = $this->app->make(ModuleRegistry::class)->providers();

        $offenders = array_values(array_filter(
            $providers,
            static fn (string $provider): bool => ! is_subclass_of($provider, ModuleServiceProvider::class),
        ));

        $this->assertSame(
            [],
            $offenders,
            'Every entry in config/modules.php must extend '.ModuleServiceProvider::class.'.',
        );
    }

    /**
     * @param  array<string, array<string, true>>  $edges
     * @return list<string>|null the first cycle found, as a path
     */
    private function firstCycle(array $edges): ?array
    {
        /** @var array<string, string> $state */
        $state = [];
        /** @var list<string> $path */
        $path = [];
        /** @var list<string>|null $found */
        $found = null;

        $walk = function (string $node) use (&$walk, &$state, &$path, &$found, $edges): void {
            if ($found !== null || ($state[$node] ?? '') === 'done') {
                return;
            }

            if (($state[$node] ?? '') === 'open') {
                $at = array_search($node, $path, true);
                $found = array_merge(array_slice($path, $at === false ? 0 : $at), [$node]);

                return;
            }

            $state[$node] = 'open';
            $path[] = $node;

            foreach (array_keys($edges[$node] ?? []) as $next) {
                $walk($next);
            }

            array_pop($path);
            $state[$node] = 'done';
        };

        foreach (array_keys($edges) as $node) {
            $walk($node);
        }

        return $found;
    }

    /**
     * @return array<string, string>
     */
    private function moduleDirectories(): array
    {
        $root = app_path('Modules');

        if (! is_dir($root)) {
            return [];
        }

        $modules = [];

        foreach (new FilesystemIterator($root) as $entry) {
            /** @var SplFileInfo $entry */
            if ($entry->isDir()) {
                $modules[$entry->getFilename()] = $entry->getPathname();
            }
        }

        return $modules;
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }
}
