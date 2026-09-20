<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Core\Audit\AuditLog;
use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SignInTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_signs_a_user_in(): void
    {
        $user = User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);

        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password'])
            ->assertOk()
            ->assertJsonPath('data.status', 'signed_in')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'ada@example.com');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_normalises_the_email_before_matching(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);

        $this->postJson('/auth/login', ['email' => '  Ada@Example.COM ', 'password' => 'secret-password'])
            ->assertOk()
            ->assertJsonPath('data.status', 'signed_in');
    }

    #[Test]
    public function a_wrong_password_and_an_unknown_account_are_indistinguishable(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);

        $wrongPassword = $this->postJson('/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'not-the-password',
        ])->assertStatus(401);

        $noSuchAccount = $this->postJson('/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'secret-password',
        ])->assertStatus(401);

        $this->assertSame($wrongPassword->getContent(), $noSuchAccount->getContent());
        $wrongPassword->assertJsonPath('error.code', 'auth.invalid_credentials');
        $this->assertGuest();
    }

    #[Test]
    public function a_suspended_account_is_refused_even_with_the_right_password(): void
    {
        User::factory()->suspended()->create([
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password'])
            ->assertForbidden()
            ->assertJsonPath('error.code', 'auth.sign_in_not_permitted')
            ->assertJsonPath('error.details.reason', 'account_suspended');

        $this->assertGuest();
    }

    #[Test]
    public function the_account_state_rule_comes_from_the_users_module_not_this_one(): void
    {
        // Auth never learns what a status is, it asks the SignInGuard.
        $user = User::factory()->suspended()->create([
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password'])
            ->assertForbidden();

        $user->update(['status' => UserStatus::Active]);

        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password'])
            ->assertOk();
    }

    #[Test]
    public function it_audits_both_outcomes(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);

        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'wrong']);
        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password']);

        $actions = AuditLog::query()->orderBy('id')->pluck('action')->all();

        $this->assertSame(['auth.sign_in_failed', 'auth.sign_in'], $actions);
    }

    #[Test]
    public function it_validates_the_payload(): void
    {
        $this->postJson('/auth/login', ['email' => 'nonsense'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    #[Test]
    public function it_signs_a_user_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/auth/logout')->assertNoContent();

        $this->assertGuest();
    }

    #[Test]
    public function the_identity_endpoint_reports_who_is_signed_in(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace']);

        $this->actingAs($user)->getJson('/auth/me')
            ->assertOk()
            ->assertJsonPath('data.name', 'Ada Lovelace')
            ->assertJsonPath('data.permissions', []);
    }

    #[Test]
    public function the_identity_endpoint_refuses_a_guest_with_the_standard_envelope(): void
    {
        $this->getJson('/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'core.unauthenticated');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
