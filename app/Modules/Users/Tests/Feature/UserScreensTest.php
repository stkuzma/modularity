<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Feature;

use App\Core\Access\AccessChecker;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class UserScreensTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_and_filters(): void
    {
        User::factory()->count(3)->create();
        User::factory()->suspended()->create(['name' => 'Suspended Person']);

        $this->get(route('users.index'))->assertOk()->assertSee('Suspended Person');

        $this->get(route('users.index', ['status' => 'suspended']))
            ->assertOk()
            ->assertSee('Suspended Person');
    }

    #[Test]
    public function it_says_so_when_a_filter_matches_nothing(): void
    {
        $this->get(route('users.index', ['search' => 'nobody-by-that-name']))
            ->assertOk()
            ->assertSee('No users match that filter.');
    }

    #[Test]
    public function the_form_creates_a_user(): void
    {
        $this->get(route('users.create'))->assertOk()->assertSee('New user');

        $this->post(route('users.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'secret-password',
            'status' => 'active',
        ])->assertRedirect(route('users.index'))->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    }

    #[Test]
    public function a_duplicate_email_comes_back_to_the_form_rather_than_as_a_json_error(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Ada',
                'email' => 'ada@example.com',
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('users.create'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function validation_errors_come_back_to_the_form(): void
    {
        $this->from(route('users.create'))
            ->post(route('users.store'), ['email' => 'nonsense'])
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function editing_without_a_password_keeps_the_current_one(): void
    {
        $user = User::factory()->create(['name' => 'Before']);
        $before = $user->password;

        $this->put(route('users.update', $user->id), [
            'name' => 'After',
            'email' => $user->email,
            'password' => '',
            'status' => 'active',
        ])->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertSame('After', $user->name);
        $this->assertSame($before, $user->password);
    }

    #[Test]
    public function it_deletes_a_user(): void
    {
        $user = User::factory()->create();

        $this->delete(route('users.destroy', $user->id))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance(AccessChecker::class, new GrantsAllAccess);
        $this->actingAs(User::factory()->create());
    }
}
