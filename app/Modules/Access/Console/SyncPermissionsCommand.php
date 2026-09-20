<?php

declare(strict_types=1);

namespace App\Modules\Access\Console;

use App\Core\Access\PermissionCatalogue;
use App\Modules\Access\Contracts\PermissionRepository;
use Illuminate\Console\Command;

final class SyncPermissionsCommand extends Command
{
    protected $signature = 'access:sync-permissions
        {--dry-run : Report what would change without writing}
        {--prune : Also delete permissions no module declares any more}';

    protected $description = 'Sync the permissions table with the permissions declared by modules';

    public function handle(PermissionCatalogue $catalogue, PermissionRepository $permissions): int
    {
        $declared = [];

        foreach ($catalogue->all() as $name => $label) {
            $declared[$name] = [
                'label' => $label,
                'module' => (string) $catalogue->moduleFor($name),
            ];
        }

        if ($declared === []) {
            $this->warn('No module declares any permission.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->table(
                ['Permission', 'Module', 'Label'],
                array_map(
                    static fn (string $name, array $meta): array => [$name, $meta['module'], $meta['label']],
                    array_keys($declared),
                    array_values($declared),
                ),
            );

            return self::SUCCESS;
        }

        // Additive by default. Removing a permission strips it from every role,
        // and during a rollout the colour still serving may depend on it.
        $result = $permissions->syncWithCatalogue($declared, (bool) $this->option('prune'));

        $this->info(sprintf(
            'Permissions synced: %d created, %d updated, %d removed.',
            $result['created'],
            $result['updated'],
            $result['removed'],
        ));

        return self::SUCCESS;
    }
}
