<?php

declare(strict_types=1);

namespace App\Core\Access;

/** What the application can do, declared by modules and synced into storage by command. */
final class PermissionCatalogue
{
    /** @var array<string, string> name => label */
    private array $permissions = [];

    /** @var array<string, string> name => module */
    private array $owners = [];

    /**
     * @param  array<string, string>  $permissions  name => label
     */
    public function declare(string $module, array $permissions): void
    {
        foreach ($permissions as $name => $label) {
            if (isset($this->owners[$name]) && $this->owners[$name] !== $module) {
                throw new DuplicatePermission(sprintf(
                    'Permission [%s] is declared by both [%s] and [%s].',
                    $name,
                    $this->owners[$name],
                    $module,
                ));
            }

            $this->permissions[$name] = $label;
            $this->owners[$name] = $module;
        }
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        ksort($this->permissions);

        return $this->permissions;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->all());
    }

    public function has(string $name): bool
    {
        return isset($this->permissions[$name]);
    }

    public function moduleFor(string $name): ?string
    {
        return $this->owners[$name] ?? null;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function groupedByModule(): array
    {
        $grouped = [];

        foreach ($this->all() as $name => $label) {
            $grouped[$this->owners[$name]][$name] = $label;
        }

        ksort($grouped);

        return $grouped;
    }
}
