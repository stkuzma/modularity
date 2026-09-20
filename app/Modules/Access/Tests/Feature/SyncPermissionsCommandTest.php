<?php

declare(strict_types=1);

namespace App\Modules\Access\Tests\Feature;

use App\Modules\Access\Models\Permission;
use App\Modules\Access\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SyncPermissionsCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_writes_what_the_modules_declare(): void
    {
        $this->command('access:sync-permissions')
            ->expectsOutputToContain('created')
            ->assertSuccessful();

        $this->assertDatabaseHas('permissions', [
            'name' => 'users.view',
            'label' => 'View users',
            'module' => 'Users',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'access.assign',
            'module' => 'Access',
        ]);
    }

    #[Test]
    public function it_is_idempotent(): void
    {
        $this->command('access:sync-permissions')->assertSuccessful();
        $first = Permission::query()->count();

        $this->command('access:sync-permissions')->assertSuccessful();

        $this->assertSame($first, Permission::query()->count());
    }

    #[Test]
    public function it_updates_a_label_that_changed_in_code(): void
    {
        $this->command('access:sync-permissions')->assertSuccessful();

        Permission::query()->where('name', 'users.view')->update(['label' => 'Stale label']);

        $this->command('access:sync-permissions')->assertSuccessful();

        $this->assertSame('View users', Permission::query()->where('name', 'users.view')->sole()->label);
    }

    #[Test]
    public function it_removes_a_permission_no_module_declares_any_more_only_when_asked(): void
    {
        $this->command('access:sync-permissions')->assertSuccessful();

        $orphan = Permission::query()->create([
            'name' => 'legacy.thing',
            'label' => 'Left over from a deleted module',
            'module' => 'Legacy',
        ]);

        $role = Role::factory()->create();
        $role->permissions()->attach($orphan->id);

        // Additive by default: an undeclared permission survives a plain sync,
        // because a rollout must not strip capabilities from the colour that
        // is still serving.
        $this->command('access:sync-permissions')->assertSuccessful();
        $this->assertDatabaseHas('permissions', ['name' => 'legacy.thing']);

        $this->command('access:sync-permissions', ['--prune' => true])->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'legacy.thing']);
        $this->assertDatabaseMissing('permission_role', ['permission_id' => $orphan->id]);
    }

    #[Test]
    public function a_dry_run_writes_nothing(): void
    {
        $this->command('access:sync-permissions', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(0, Permission::query()->count());
    }
}
