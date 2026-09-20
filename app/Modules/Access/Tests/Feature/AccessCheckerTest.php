<?php

declare(strict_types=1);

namespace App\Modules\Access\Tests\Feature;

use App\Core\Access\AccessChecker;
use App\Modules\Access\Contracts\PermissionRepository;
use App\Modules\Access\Contracts\RoleRepository;
use App\Modules\Access\Models\Role;
use App\Modules\Access\Services\DatabaseAccessChecker;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AccessCheckerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function enabling_the_module_replaces_the_fail_closed_default(): void
    {
        $this->assertInstanceOf(DatabaseAccessChecker::class, $this->app->make(AccessChecker::class));
    }

    #[Test]
    public function a_user_inherits_the_permissions_of_their_roles(): void
    {
        $user = $this->userWithPermissions(['users.view', 'users.create']);

        $checker = $this->app->make(AccessChecker::class);

        $this->assertTrue($checker->allows($user, 'users.view'));
        $this->assertTrue($checker->allows($user, 'users.create'));
        $this->assertFalse($checker->allows($user, 'users.delete'));
        $this->assertSame(['users.create', 'users.view'], $checker->permissionsFor($user));
    }

    #[Test]
    public function a_user_with_no_role_has_nothing(): void
    {
        $user = User::factory()->create();

        $this->assertSame([], $this->app->make(AccessChecker::class)->permissionsFor($user));
        $this->assertFalse(Gate::forUser($user)->allows('users.view'));
    }

    #[Test]
    public function permissions_from_two_roles_are_merged_without_duplicates(): void
    {
        $this->command('access:sync-permissions');

        $user = User::factory()->create();
        $roles = $this->app->make(RoleRepository::class);

        $viewer = Role::factory()->create(['name' => 'viewer']);
        $editor = Role::factory()->create(['name' => 'editor']);

        $roles->syncPermissions($viewer, ['users.view']);
        $roles->syncPermissions($editor, ['users.view', 'users.update']);
        $roles->assignToUser($viewer, $user->id);
        $roles->assignToUser($editor, $user->id);

        $this->assertSame(
            ['users.update', 'users.view'],
            $this->app->make(AccessChecker::class)->permissionsFor($user),
        );
    }

    #[Test]
    public function revoking_a_role_removes_its_permissions(): void
    {
        $user = $this->userWithPermissions(['users.view']);
        $roles = $this->app->make(RoleRepository::class);
        $role = $roles->findByName('granted');

        $this->assertNotNull($role);
        $roles->revokeFromUser($role, $user->id);

        // A fresh checker: the memo is request scoped by design.
        $this->assertSame([], (new DatabaseAccessChecker(
            $this->app->make(PermissionRepository::class),
        ))->permissionsFor($user));
    }

    #[Test]
    public function a_gated_endpoint_accepts_a_user_whose_role_grants_it(): void
    {
        $user = $this->userWithPermissions(['users.view']);

        $this->actingAs($user)->getJson('/api/users')->assertOk();
        $this->actingAs($user)->deleteJson('/api/users/'.$user->id)->assertForbidden();
    }

    #[Test]
    public function it_reports_the_roles_a_user_holds(): void
    {
        $user = $this->userWithPermissions(['users.view']);

        $this->assertSame(['granted'], $this->app->make(RoleRepository::class)->roleNamesForUser($user->id));
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $this->command('access:sync-permissions');

        $user = User::factory()->create();
        $roles = $this->app->make(RoleRepository::class);
        $role = Role::factory()->create(['name' => 'granted', 'label' => 'Granted']);

        $roles->syncPermissions($role, $permissions);
        $roles->assignToUser($role, $user->id);

        return $user;
    }
}
