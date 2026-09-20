<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Repositories;

use App\Modules\Invitations\Contracts\InvitationRepository;
use App\Modules\Invitations\Data\InviteData;
use App\Modules\Invitations\Models\Invitation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class EloquentInvitationRepository implements InvitationRepository
{
    public function findById(int $id): ?Invitation
    {
        return Invitation::query()->find($id);
    }

    public function findByTokenHash(string $hash): ?Invitation
    {
        return Invitation::query()->where('token_hash', $hash)->first();
    }

    public function hasPendingFor(string $email): bool
    {
        return Invitation::query()
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    public function pending(): Collection
    {
        return Invitation::query()
            ->whereNull('accepted_at')
            ->orderByDesc('created_at')
            ->get()
            ->toBase();
    }

    public function create(InviteData $data, string $tokenHash): Invitation
    {
        $invitation = new Invitation;

        $invitation->email = $data->email;
        $invitation->token_hash = $tokenHash;
        $invitation->invited_by = $data->invitedBy;
        $invitation->expires_at = Carbon::now()->addDays($data->validForDays);
        $invitation->save();

        return $invitation;
    }

    public function markAccepted(Invitation $invitation): Invitation
    {
        $invitation->accepted_at = Carbon::now();
        $invitation->save();

        return $invitation;
    }

    public function delete(Invitation $invitation): void
    {
        $invitation->delete();
    }
}
