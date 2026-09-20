<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use App\Core\Access\AccessChecker;
use App\Core\Access\DeniesEverything;
use App\Core\Access\PermissionCatalogue;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class AccessSeamTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function every_declared_permission_becomes_a_gate_ability(): void
    {
        foreach ($this->app->make(PermissionCatalogue::class)->names() as $permission) {
            $this->assertTrue(
                Gate::has($permission),
                "No gate ability was defined for [{$permission}].",
            );
        }
    }

    #[Test]
    public function the_gate_delegates_to_whatever_checker_is_bound(): void
    {
        $user = User::factory()->create();

        $this->app->instance(AccessChecker::class, new GrantsAllAccess(['users.view']));

        $this->assertTrue(Gate::forUser($user)->allows('users.view'));
        $this->assertFalse(Gate::forUser($user)->allows('users.delete'));
    }

    #[Test]
    public function the_default_checker_fails_closed(): void
    {
        $user = User::factory()->create();

        $this->app->instance(AccessChecker::class, new DeniesEverything);

        $this->assertFalse(Gate::forUser($user)->allows('users.view'));
        $this->assertSame([], $this->app->make(AccessChecker::class)->permissionsFor($user));
    }

    #[Test]
    public function a_gated_route_refuses_an_unauthenticated_caller(): void
    {
        $this->getJson('/api/users')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'core.forbidden');
    }
}
