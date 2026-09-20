<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Core\Access\AccessChecker;
use App\Core\Access\DeniesEverything;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class ShellTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_guest_cannot_reach_the_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect();
        $this->assertGuest();
    }

    #[Test]
    public function the_dashboard_reports_the_identity_and_its_permissions(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

        $this->app->instance(AccessChecker::class, new GrantsAllAccess(['users.view']));

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Ada Lovelace')
            ->assertSee('ada@example.com')
            ->assertSee('users.view');
    }

    #[Test]
    public function an_account_with_no_permissions_is_told_so_rather_than_shown_an_empty_box(): void
    {
        // The core's own fail-closed checker: no roles module, no permissions.
        $this->app->instance(AccessChecker::class, new DeniesEverything);

        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertOk()
            ->assertSee('Permissions arrive through roles');
    }

    #[Test]
    public function the_navigation_only_offers_what_the_caller_may_do(): void
    {
        $this->app->instance(AccessChecker::class, new GrantsAllAccess(['users.view']));

        // Labels, not route(), so this test needs no module installed.
        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertOk()
            ->assertSee('Users')
            ->assertDontSee('Roles');
    }

    #[Test]
    public function a_refused_permission_lands_somewhere_the_caller_is_allowed_to_be(): void
    {
        $this->app->instance(AccessChecker::class, new GrantsAllAccess(['users.view']));

        $this->actingAs(User::factory()->create())
            ->post(route('users.store'), [
                'name' => 'X',
                'email' => 'x@example.com',
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
