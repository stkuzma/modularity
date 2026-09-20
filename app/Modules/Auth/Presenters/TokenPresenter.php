<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presenters;

use App\Core\Pipeline\Presenter;
use App\Modules\Auth\Models\AuthToken;

/**
 * @implements Presenter<AuthToken>
 */
final readonly class TokenPresenter implements Presenter
{
    public function present(mixed $subject): array
    {
        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'last_used_at' => $subject->last_used_at?->toAtomString(),
            'expires_at' => $subject->expires_at?->toAtomString(),
            'created_at' => $subject->created_at?->toAtomString(),
        ];
    }

    /**
     * @param  iterable<AuthToken>  $subjects
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
