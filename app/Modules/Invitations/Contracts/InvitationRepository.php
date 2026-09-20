<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Contracts;

use App\Modules\Invitations\Data\InviteData;
use App\Modules\Invitations\Models\Invitation;
use Illuminate\Support\Collection;

interface InvitationRepository
{
    public function findById(int $id): ?Invitation;

    public function findByTokenHash(string $hash): ?Invitation;

    public function hasPendingFor(string $email): bool;

    /**
     * @return Collection<int, Invitation>
     */
    public function pending(): Collection;

    public function create(InviteData $data, string $tokenHash): Invitation;

    public function markAccepted(Invitation $invitation): Invitation;

    public function delete(Invitation $invitation): void;
}
