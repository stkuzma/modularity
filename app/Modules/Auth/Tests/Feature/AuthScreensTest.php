<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Core\Access\AccessChecker;
use App\Modules\Auth\Models\AuthToken;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class AuthScreensTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_guest_is_sent_to_the_sign_in_screen(): void
    {
        $this->get('/')->assertRedirect(route('sign-in'));
        $this->get('/dashboard')->assertRedirect(route('sign-in'));
        $this->get(route('sign-in'))->assertOk()->assertSee('Sign in');
    }

    #[Test]
    public function the_form_signs_a_user_in_and_lands_on_the_dashboard(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);

        $this->post(route('sign-in.submit'), [
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'));

        $this->get('/dashboard')->assertOk();
    }

    #[Test]
    public function a_bad_password_comes_back_to_the_form_with_the_message(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);

        $this->from(route('sign-in'))
            ->post(route('sign-in.submit'), ['email' => 'ada@example.com', 'password' => 'wrong'])
            ->assertRedirect(route('sign-in'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    #[Test]
    public function the_challenge_screen_is_unreachable_without_a_pending_sign_in(): void
    {
        $this->get(route('second-factor.show'))->assertRedirect(route('sign-in'));
    }

    #[Test]
    public function the_security_screen_enrols_and_issues_a_token(): void
    {
        $user = User::factory()->create();

        $this->app->instance(AccessChecker::class, new GrantsAllAccess);
        $this->actingAs($user);

        $this->get(route('security.show'))->assertOk()->assertSee('Not enrolled');

        $this->post(route('security.second-factor.enrol'))
            ->assertRedirect()
            ->assertSessionHas('mfa_secret')
            ->assertSessionHas('mfa_recovery_codes')
            ->assertSessionHas('mfa_qr');

        $this->get(route('security.show'))
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertSee('Scan this with your authenticator app');

        $this->post(route('security.tokens.store'), ['name' => 'ci-runner'])
            ->assertRedirect(route('security.show'))
            ->assertSessionHas('issued_token');

        $this->get(route('security.show'))->assertOk()->assertSee('ci-runner');
        $this->assertDatabaseHas('auth_tokens', ['user_id' => $user->id, 'name' => 'ci-runner']);
    }

    #[Test]
    public function a_token_can_be_revoked_from_the_screen(): void
    {
        $this->app->instance(AccessChecker::class, new GrantsAllAccess);
        $this->actingAs(User::factory()->create());

        $this->post(route('security.tokens.store'), ['name' => 'ci-runner']);
        $id = AuthToken::query()->sole()->id;

        $this->delete(route('security.tokens.destroy', $id))
            ->assertRedirect(route('security.show'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('auth_tokens', ['id' => $id]);
    }

    #[Test]
    public function signing_out_returns_to_the_sign_in_screen(): void
    {
        $this->app->instance(AccessChecker::class, new GrantsAllAccess);
        $this->actingAs(User::factory()->create());

        $this->post(route('web.logout'))->assertRedirect(route('sign-in'));

        $this->assertGuest();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
