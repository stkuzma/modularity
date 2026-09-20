<?php

declare(strict_types=1);

namespace App\Modules\Access\Contracts;

use App\Modules\Access\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepository
{
    /**
     * @return Collection<int, Permission>
     */
    public function all(): Collection;

    /**
     * @return list<string>
     */
    public function names(): array;

    /**
     * @param  array<string, array{label: string, module: string}>  $declared
     * @return array{created: int, updated: int, removed: int}
     */
    public function syncWithCatalogue(array $declared, bool $prune = false): array;

    /**
     * @return list<string>
     */
    public function permissionNamesForUser(int $userId): array;
}
