<?php

declare(strict_types=1);

namespace App\Modules\Access\Presenters;

use App\Core\Pipeline\Presenter;
use App\Modules\Access\Models\Permission;

/**
 * @implements Presenter<Permission>
 */
final readonly class PermissionPresenter implements Presenter
{
    public function present(mixed $subject): array
    {
        return [
            'name' => $subject->name,
            'label' => $subject->label,
            'module' => $subject->module,
        ];
    }

    /**
     * @param  iterable<Permission>  $subjects
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
