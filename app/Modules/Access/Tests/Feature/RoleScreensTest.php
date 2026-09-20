<?php

declare(strict_types=1);

namespace App\Modules\Access\Tests\Feature;

use App\Core\Access\AccessChecker;
use App\Modules\Access\Models\Role;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class RoleScreensTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_edit_form_groups_permissions_by_the_module_that_declares_them(): void
    {
        $role = Role::factory()->create(['name' => 'support', 'label' => 'Support']);

        $this->get(route('roles.edit', $role->id))
            ->assertOk()
            ->assertSee('users.view')
            ->assertSee('View users')
            ->assertSee('Users')
            ->assertSee('Access');
    }

    #[Test]
    public function one_form_saves_the_role_and_its_permissions(): void
    {
        $role = Role::factory()->create(['name' => 'support', 'label' => 'Support']);

        $this->put(route('roles.update', $role->id), [
            'name' => 'support',
            'label' => 'Support team',
            'permissions' => ['users.view', 'users.update'],
        ])->assertRedirect(route('roles.index'));

        $this->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Support team')
            ->assertSee('users.update');
    }

    #[Test]
    public function a_permission_no_module_declares_comes_back_as_a_message(): void
    {
        $role = Role::factory()->create();

        $this->from(route('roles.edit', $role->id))
            ->put(route('roles.update', $role->id), [
                'name' => $role->name,
                'label' => $role->label,
                'permissions' => ['users.invent'],
            ])
            ->assertRedirect(route('roles.edit', $role->id))
            ->assertSessionHas('error');
    }

    #[Test]
    public function a_system_role_offers_no_edit_or_delete_controls(): void
    {
        Role::factory()->system()->create(['name' => 'administrator', 'label' => 'Administrator']);

        $this->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Administrator')
            ->assertSee('system')
            ->assertDontSee('Delete');
    }

    #[Test]
    public function creating_a_role_lands_on_its_permission_form(): void
    {
        $this->post(route('roles.store'), ['name' => 'support-lead', 'label' => 'Support lead'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('roles', ['name' => 'support-lead']);
    }

    #[Test]
    public function a_caller_who_may_only_view_gets_no_write_controls(): void
    {
        Role::factory()->create(['label' => 'Support']);

        $this->app->instance(AccessChecker::class, new GrantsAllAccess(['access.roles.view']));

        $this->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Support')
            ->assertDontSee(route('roles.create'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance(AccessChecker::class, new GrantsAllAccess);
        $this->actingAs(User::factory()->create());
        $this->command('access:sync-permissions')->assertSuccessful();
    }
}
