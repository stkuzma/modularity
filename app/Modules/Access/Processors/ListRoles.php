<?php

declare(strict_types=1);

namespace App\Modules\Access\Processors;

use App\Core\Pipeline\Processor;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * @implements Processor<null, Collection<int, Role>>
 */
final readonly class ListRoles implements Processor
{
    public function __construct(private RoleRepository $roles) {}

    public function process(mixed $input = null): Collection
    {
        return $this->roles->all();
    }
}
