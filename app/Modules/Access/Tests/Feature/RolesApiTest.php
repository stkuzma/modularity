<?php

declare(strict_types=1);

namespace App\Modules\Access\Tests\Feature;

use App\Core\Access\AccessChecker;
use App\Modules\Access\Models\Role;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class RolesApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_roles_with_their_permissions(): void
    {
        $this->command('access:sync-permissions')->assertSuccessful();

        $role = Role::factory()->create(['name' => 'support', 'label' => 'Support']);
        $this->putJson("/api/roles/{$role->id}/permissions", ['permissions' => ['users.view']])
            ->assertOk();

        $this->getJson('/api/roles')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'support')
            ->assertJsonPath('data.0.permissions', ['users.view']);
    }

    #[Test]
    public function it_creates_a_role(): void
    {
        $this->postJson('/api/roles', ['name' => 'support-lead', 'label' => 'Support lead'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'support-lead')
            ->assertJsonPath('data.is_system', false)
            ->assertJsonPath('data.permissions', []);

        $this->assertDatabaseHas('roles', ['name' => 'support-lead']);
    }

    #[Test]
    public function it_refuses_a_duplicate_role_name(): void
    {
        Role::factory()->create(['name' => 'support']);

        $this->postJson('/api/roles', ['name' => 'support', 'label' => 'Support'])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'access.role_name_taken');
    }

    #[Test]
    public function it_requires_a_slug_for_a_role_name(): void
    {
        $this->postJson('/api/roles', ['name' => 'Support Lead', 'label' => 'Support lead'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_protects_a_system_role_from_edits_and_deletion(): void
    {
        $role = Role::factory()->system()->create(['name' => 'administrator']);

        $this->patchJson("/api/roles/{$role->id}", ['name' => 'renamed', 'label' => 'Renamed'])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'access.system_role_protected');

        $this->deleteJson("/api/roles/{$role->id}")
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'access.system_role_protected');

        $this->assertDatabaseHas('roles', ['name' => 'administrator']);
    }

    #[Test]
    public function it_refuses_a_permission_no_module_declares(): void
    {
        $role = Role::factory()->create();

        $this->putJson("/api/roles/{$role->id}/permissions", ['permissions' => ['users.invent']])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'access.unknown_permission')
            ->assertJsonPath('error.details.permissions', ['users.invent']);
    }

    #[Test]
    public function it_deletes_a_role(): void
    {
        $role = Role::factory()->create();

        $this->deleteJson("/api/roles/{$role->id}")->assertNoContent();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    #[Test]
    public function it_assigns_and_revokes_a_role_for_a_user(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $this->postJson("/api/roles/{$role->id}/users", ['user_id' => $user->id])->assertNoContent();
        $this->assertDatabaseHas('role_user', ['role_id' => $role->id, 'user_id' => $user->id]);

        $this->deleteJson("/api/roles/{$role->id}/users", ['user_id' => $user->id])->assertNoContent();
        $this->assertDatabaseMissing('role_user', ['role_id' => $role->id, 'user_id' => $user->id]);
    }

    #[Test]
    public function assigning_a_role_twice_is_idempotent(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $this->postJson("/api/roles/{$role->id}/users", ['user_id' => $user->id])->assertNoContent();
        $this->postJson("/api/roles/{$role->id}/users", ['user_id' => $user->id])->assertNoContent();

        $this->assertSame(1, DB::table('role_user')->count());
    }

    #[Test]
    public function a_caller_without_the_manage_permission_cannot_write(): void
    {
        $this->app->instance(AccessChecker::class, new GrantsAllAccess(['access.roles.view']));

        $this->getJson('/api/roles')->assertOk();

        $this->postJson('/api/roles', ['name' => 'nope', 'label' => 'Nope'])
            ->assertForbidden()
            ->assertJsonPath('error.details.permission', 'access.roles.manage');
    }

    #[Test]
    public function the_permission_catalogue_is_readable_but_not_writable(): void
    {
        $this->command('access:sync-permissions')->assertSuccessful();

        $this->getJson('/api/permissions')
            ->assertOk()
            ->assertJsonFragment(['name' => 'users.view', 'label' => 'View users', 'module' => 'Users']);

        $this->postJson('/api/permissions', ['name' => 'anything'])->assertStatus(405);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(AccessChecker::class, new GrantsAllAccess);
        $this->actingAs(User::factory()->create());
    }
}
