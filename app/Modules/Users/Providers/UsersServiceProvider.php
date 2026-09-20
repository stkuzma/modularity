<?php

declare(strict_types=1);

namespace App\Modules\Users\Providers;

use App\Core\Auth\SignInGuard;
use App\Core\Module\ModuleServiceProvider;
use App\Core\Navigation\NavigationItem;
use App\Modules\Users\Contracts\Accounts;
use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Models\User;
use App\Modules\Users\Policies\UserPolicy;
use App\Modules\Users\Repositories\EloquentUserRepository;
use App\Modules\Users\Services\AccountService;
use App\Modules\Users\Services\StatusSignInGuard;

final class UsersServiceProvider extends ModuleServiceProvider
{
    protected function bindings(): array
    {
        return [
            UserRepository::class => EloquentUserRepository::class,
            Accounts::class => AccountService::class,
            SignInGuard::class => StatusSignInGuard::class,
        ];
    }

    protected function navigation(): array
    {
        return [
            new NavigationItem(label: 'Users', route: 'users.index', permission: 'users.view', order: 10),
        ];
    }

    protected function permissions(): array
    {
        return [
            'users.view' => 'View users',
            'users.create' => 'Create users',
            'users.update' => 'Edit users',
            'users.delete' => 'Delete users',
        ];
    }

    protected function policies(): array
    {
        return [
            User::class => UserPolicy::class,
        ];
    }
}
