<?php

declare(strict_types=1);

namespace App\Modules\Access\Presenters;

use App\Core\Pipeline\Presenter;
use App\Modules\Access\Models\Permission;
use App\Modules\Access\Models\Role;

/**
 * @implements Presenter<Role>
 */
final readonly class RolePresenter implements Presenter
{
    public function present(mixed $subject): array
    {
        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'label' => $subject->label,
            'is_system' => $subject->is_system,
            'permissions' => $subject->permissions
                ->map(static fn (Permission $permission): string => $permission->name)
                ->sort()
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  iterable<Role>  $subjects
     * @return list<array<string, mixed>>
     */
    public function collection(iterable $subjects): array
    {
        $presented = [];

        foreach ($subjects as $subject) {
            $presented[] = $this->present($subject);
        }

        return $presented;
    }
}
