<?php

declare(strict_types=1);

namespace App\Core\Access;

use Illuminate\Contracts\Auth\Authenticatable;

/** May this actor do this thing. Bound to DeniesEverything until a module answers. */
interface AccessChecker
{
    public function allows(Authenticatable $actor, string $permission): bool;

    /**
     * @return list<string>
     */
    public function permissionsFor(Authenticatable $actor): array;
}
