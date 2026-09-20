<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Presenters;

use App\Core\Pipeline\Presenter;
use App\Modules\Invitations\Models\Invitation;

/**
 * @implements Presenter<Invitation>
 */
final readonly class InvitationPresenter implements Presenter
{
    public function present(mixed $subject): array
    {
        return [
            'id' => $subject->id,
            'email' => $subject->email,
            'expires_at' => $subject->expires_at->toAtomString(),
            'expired' => $subject->hasExpired(),
            'accepted' => $subject->isAccepted(),
        ];
    }

    /**
     * @param  iterable<Invitation>  $subjects
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
