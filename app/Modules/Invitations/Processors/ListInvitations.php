<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Processors;

use App\Core\Pipeline\Processor;
use App\Modules\Invitations\Contracts\InvitationRepository;
use App\Modules\Invitations\Models\Invitation;
use Illuminate\Support\Collection;

/**
 * @implements Processor<null, Collection<int, Invitation>>
 */
final readonly class ListInvitations implements Processor
{
    public function __construct(private InvitationRepository $invitations) {}

    public function process(mixed $input = null): Collection
    {
        return $this->invitations->pending();
    }
}
