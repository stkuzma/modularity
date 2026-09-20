<?php

declare(strict_types=1);

use App\Modules\Access\Providers\AccessServiceProvider;
use App\Modules\Auth\Providers\AuthServiceProvider;
use App\Modules\Invitations\Providers\InvitationsServiceProvider;
use App\Modules\Users\Providers\UsersServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled modules
    |--------------------------------------------------------------------------
    |
    | The single switchboard. Every entry must be a class extending
    | App\Core\Module\ModuleServiceProvider; anything else fails at boot with
    | an explicit message rather than being quietly ignored.
    |
    | Order matters only where one module's bindings are consumed by another
    | module's onRegister(). Prefer bindings that are resolved lazily so the
    | order does not matter at all.
    |
    */

    'enabled' => [
        UsersServiceProvider::class,
        AccessServiceProvider::class,
        AuthServiceProvider::class,
        InvitationsServiceProvider::class,
    ],

];
