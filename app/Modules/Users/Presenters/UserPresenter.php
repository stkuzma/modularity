<?php

declare(strict_types=1);

namespace App\Modules\Users\Presenters;

use App\Core\Pipeline\Presenter;
use App\Modules\Users\Models\User;

/**
 * @implements Presenter<User>
 */
final readonly class UserPresenter implements Presenter
{
    public function present(mixed $subject): array
    {
        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'email' => $subject->email,
            'status' => $subject->status->value,
            'email_verified' => $subject->email_verified_at !== null,
            'created_at' => $subject->created_at?->toAtomString(),
        ];
    }

    /**
     * @param  iterable<User>  $subjects
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
