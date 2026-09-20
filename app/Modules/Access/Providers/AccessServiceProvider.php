<?php

declare(strict_types=1);

namespace App\Modules\Access\Providers;

use App\Core\Access\AccessChecker;
use App\Core\Module\ModuleServiceProvider;
use App\Core\Navigation\NavigationItem;
use App\Modules\Access\Console\SyncPermissionsCommand;
use App\Modules\Access\Contracts\PermissionRepository;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Repositories\EloquentPermissionRepository;
use App\Modules\Access\Repositories\EloquentRoleRepository;
use App\Modules\Access\Services\DatabaseAccessChecker;

final class AccessServiceProvider extends ModuleServiceProvider
{
    protected function bindings(): array
    {
        return [
            RoleRepository::class => EloquentRoleRepository::class,
            PermissionRepository::class => EloquentPermissionRepository::class,
            AccessChecker::class => DatabaseAccessChecker::class,
        ];
    }

    protected function navigation(): array
    {
        return [
            new NavigationItem(label: 'Roles', route: 'roles.index', permission: 'access.roles.view', order: 20),
        ];
    }

    protected function permissions(): array
    {
        return [
            'access.roles.view' => 'View roles and the permission catalogue',
            'access.roles.manage' => 'Create, edit and delete roles',
            'access.assign' => 'Assign roles to users',
        ];
    }

    protected function onRegister(): void
    {
        // Memoised per request, so one instance per request.
        $this->app->singleton(AccessChecker::class, DatabaseAccessChecker::class);
    }

    protected function onBoot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([SyncPermissionsCommand::class]);
        }
    }
}
