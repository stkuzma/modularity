<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Access\PermissionCatalogue;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Models\Role;
use App\Modules\Users\Contracts\UserRepository;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(PermissionCatalogue $catalogue, RoleRepository $roles, UserRepository $users): void
    {
        $this->call(PermissionSeeder::class);

        $administrator = Role::query()->firstOrCreate(
            ['name' => 'administrator'],
            ['label' => 'Administrator', 'is_system' => true],
        );

        $roles->syncPermissions($administrator, $catalogue->names());

        $user = User::query()->where('email', 'admin@example.test')->first()
            ?? $users->create(new CreateUserData(
                name: 'Administrator',
                email: 'admin@example.test',
                password: 'password',
            ));

        $roles->assignToUser($administrator, $user->id);

        $this->command->info('Sign in with admin@example.test / password');
    }
}
