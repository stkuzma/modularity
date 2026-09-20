<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Feature;

use App\Core\Access\AccessChecker;
use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class UsersApiTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    #[Test]
    public function it_lists_users_with_pagination_meta(): void
    {
        User::factory()->count(3)->create();

        $this->getJson('/api/users?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 4)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.last_page', 2);
    }

    #[Test]
    public function it_refuses_a_caller_without_the_permission(): void
    {
        $this->app->instance(AccessChecker::class, new GrantsAllAccess(['users.view']));

        $this->postJson('/api/users', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ])
            ->assertForbidden()
            ->assertJsonPath('error.code', 'core.forbidden')
            ->assertJsonPath('error.details.permission', 'users.create');
    }

    #[Test]
    public function it_filters_the_list_by_status(): void
    {
        User::factory()->suspended()->create();

        $this->getJson('/api/users?status=suspended')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'suspended');
    }

    #[Test]
    public function it_creates_a_user_and_never_returns_the_password(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Ada Lovelace',
            'email' => 'Ada@Example.com ',
            'password' => 'secret-password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Ada Lovelace')
            ->assertJsonPath('data.email', 'ada@example.com')
            ->assertJsonPath('data.status', 'active');

        $this->assertStringNotContainsString('password', (string) $response->getContent());
        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);

        $stored = User::query()->where('email', 'ada@example.com')->sole();
        $this->assertNotSame('secret-password', $stored->password);
    }

    #[Test]
    public function a_password_never_reaches_storage_or_a_response_in_plaintext(): void
    {
        $secret = 'correct-horse-battery-staple';

        $response = $this->postJson('/api/users', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => $secret,
        ])->assertCreated();

        $this->assertStringNotContainsString($secret, (string) $response->getContent());

        $stored = DB::table('users')->where('email', 'ada@example.com')->sole();

        $this->assertNotSame($secret, $stored->password);
        $this->assertTrue(Hash::check($secret, $stored->password));

        foreach (DB::table('audit_logs')->get() as $entry) {
            $this->assertStringNotContainsString($secret, json_encode($entry, JSON_THROW_ON_ERROR));
        }
    }

    #[Test]
    public function it_rejects_a_duplicate_email_with_the_error_envelope(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->postJson('/api/users', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'users.email_taken')
            ->assertJsonPath('error.details.email', 'ada@example.com');
    }

    #[Test]
    public function it_validates_the_payload(): void
    {
        $this->postJson('/api/users', ['email' => 'not-an-email', 'password' => 'short'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function it_shows_a_user(): void
    {
        $user = User::factory()->create(['name' => 'Grace Hopper']);

        $this->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Grace Hopper');
    }

    #[Test]
    public function an_unknown_user_is_a_404_with_a_stable_code(): void
    {
        $this->getJson('/api/users/404')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'users.not_found')
            ->assertJsonPath('error.details.id', 404);
    }

    #[Test]
    public function it_updates_a_user(): void
    {
        $user = User::factory()->create();

        $this->patchJson("/api/users/{$user->id}", ['name' => 'Ada Lovelace', 'status' => 'suspended'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Ada Lovelace')
            ->assertJsonPath('data.status', 'suspended');

        $this->assertSame(UserStatus::Suspended, $user->refresh()->status);
    }

    #[Test]
    public function it_deletes_a_user(): void
    {
        $user = User::factory()->create();

        $this->deleteJson("/api/users/{$user->id}")->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Permissive double; these tests do not need the Access module.
        $this->app->instance(AccessChecker::class, new GrantsAllAccess);

        $this->actor = User::factory()->create(['name' => 'Acting User']);
        $this->actingAs($this->actor);
    }
}
