<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use App\Core\Access\AccessChecker;
use App\Core\Navigation\Navigation;
use App\Core\Navigation\NavigationItem;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class NavigationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_shell_contributes_the_dashboard_and_modules_contribute_the_rest(): void
    {
        // Properties, not a label list: that breaks whenever a module changes.
        $items = $this->app->make(Navigation::class)->visibleTo($this->actor());

        $this->assertSame('Dashboard', $items[0]->label);
        $this->assertGreaterThan(1, count($items), 'No module contributed a navigation entry.');
    }

    #[Test]
    public function entries_come_back_in_declared_order(): void
    {
        $orders = array_map(
            static fn (NavigationItem $item): int => $item->order,
            $this->app->make(Navigation::class)->visibleTo($this->actor()),
        );

        $sorted = $orders;
        sort($sorted);

        $this->assertSame($sorted, $orders);
    }

    #[Test]
    public function an_entry_whose_permission_the_actor_lacks_is_not_offered(): void
    {
        $actor = $this->actor(['users.view']);

        $labels = array_map(
            static fn (NavigationItem $item): string => $item->label,
            $this->app->make(Navigation::class)->visibleTo($actor),
        );

        $this->assertContains('Users', $labels);
        $this->assertNotContains('Roles', $labels);
    }

    #[Test]
    public function an_entry_pointing_at_a_route_that_does_not_exist_is_dropped(): void
    {
        $navigation = $this->app->make(Navigation::class);
        $navigation->add([new NavigationItem(label: 'Ghost', route: 'no.such.route')]);

        $labels = array_map(
            static fn (NavigationItem $item): string => $item->label,
            $navigation->visibleTo($this->actor()),
        );

        $this->assertNotContains('Ghost', $labels);
    }

    #[Test]
    public function a_guest_sees_only_entries_that_need_no_permission(): void
    {
        $items = $this->app->make(Navigation::class)->visibleTo(null);

        foreach ($items as $item) {
            $this->assertNull($item->permission, "[{$item->label}] was offered to a guest.");
        }
    }

    #[Test]
    public function the_shell_renders_what_the_registry_returns(): void
    {
        $this->actingAs($this->actor(['users.view']))->get('/dashboard')
            ->assertOk()
            ->assertSee('Users')
            ->assertDontSee('Roles');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function actor(array $permissions = []): User
    {
        $this->app->instance(AccessChecker::class, new GrantsAllAccess($permissions));

        return User::factory()->create();
    }
}
