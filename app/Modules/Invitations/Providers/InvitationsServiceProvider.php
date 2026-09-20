<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Providers;

use App\Core\Module\ModuleServiceProvider;
use App\Core\Navigation\NavigationItem;
use App\Modules\Invitations\Contracts\InvitationRepository;
use App\Modules\Invitations\Repositories\EloquentInvitationRepository;

final class InvitationsServiceProvider extends ModuleServiceProvider
{
    protected function bindings(): array
    {
        return [
            InvitationRepository::class => EloquentInvitationRepository::class,
        ];
    }

    protected function navigation(): array
    {
        return [
            new NavigationItem(label: 'Invitations', route: 'invitations.index', permission: 'invitations.view', order: 15),
        ];
    }

    protected function permissions(): array
    {
        return [
            'invitations.view' => 'View pending invitations',
            'invitations.send' => 'Invite people and revoke invitations',
        ];
    }
}
