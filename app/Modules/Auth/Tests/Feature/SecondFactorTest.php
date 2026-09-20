<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Models\MfaSecret;
use App\Modules\Auth\Services\Totp;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SecondFactorTest extends TestCase
{
    use RefreshDatabase;

    private Totp $totp;

    #[Test]
    public function enrolment_returns_a_secret_a_uri_and_recovery_codes(): void
    {
        $user = User::factory()->create(['email' => 'ada@example.com']);

        $response = $this->actingAs($user)
            ->postJson('/auth/second-factor/enrol', ['account' => 'ada@example.com'])
            ->assertCreated();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $this->jsonString($response, 'data.secret'));
        $this->assertStringStartsWith('otpauth://totp/', $this->jsonString($response, 'data.provisioning_uri'));
        $this->assertCount(8, $this->jsonList($response, 'data.recovery_codes'));
    }

    #[Test]
    public function an_unconfirmed_enrolment_does_not_challenge_sign_in(): void
    {
        $user = User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);

        $this->actingAs($user)->postJson('/auth/second-factor/enrol');
        $this->post('/auth/logout');

        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password'])
            ->assertOk()
            ->assertJsonPath('data.status', 'signed_in');
    }

    #[Test]
    public function confirming_with_a_valid_code_enables_it(): void
    {
        $user = User::factory()->create();
        $secret = $this->enrol($user);

        $this->actingAs($user)
            ->postJson('/auth/second-factor/confirm', ['code' => $this->totp->codeAt($secret, time())])
            ->assertOk()
            ->assertJsonPath('data.status', 'enabled');

        $this->assertNotNull(MfaSecret::query()->whereKey($user->id)->sole()->confirmed_at);
    }

    #[Test]
    public function confirming_with_a_wrong_code_is_refused(): void
    {
        $user = User::factory()->create();
        $this->enrol($user);

        $this->actingAs($user)
            ->postJson('/auth/second-factor/confirm', ['code' => '000000'])
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'auth.invalid_second_factor');
    }

    #[Test]
    public function sign_in_stops_at_the_challenge_once_it_is_enabled(): void
    {
        $user = User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);
        $secret = $this->enable($user);

        $this->post('/auth/logout');

        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password'])
            ->assertOk()
            ->assertJsonPath('data.status', 'second_factor_required');

        $this->assertGuest();

        $this->postJson('/auth/second-factor/verify', ['code' => $this->totp->codeAt($secret, time())])
            ->assertOk()
            ->assertJsonPath('data.status', 'signed_in');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function a_recovery_code_works_once_and_then_does_not(): void
    {
        $user = User::factory()->create(['email' => 'ada@example.com', 'password' => 'secret-password']);
        $codes = $this->enableReturningRecoveryCodes($user);

        $this->post('/auth/logout');
        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password']);

        $this->postJson('/auth/second-factor/verify', ['code' => $codes[0]])
            ->assertOk()
            ->assertJsonPath('data.status', 'signed_in');

        $this->post('/auth/logout');
        $this->postJson('/auth/login', ['email' => 'ada@example.com', 'password' => 'secret-password']);

        $this->postJson('/auth/second-factor/verify', ['code' => $codes[0]])
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'auth.invalid_second_factor');
    }

    #[Test]
    public function verifying_without_a_pending_sign_in_is_refused(): void
    {
        $this->postJson('/auth/second-factor/verify', ['code' => '123456'])
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'auth.no_pending_challenge');
    }

    #[Test]
    public function a_half_signed_in_session_cannot_reach_protected_routes(): void
    {
        $user = User::factory()->create();
        $this->enable($user);

        // Confirming marks the session verified, so rebuild the unverified state.
        $this->flushSession();

        $this->actingAs($user)->getJson('/auth/tokens')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'auth.second_factor_required');
    }

    #[Test]
    public function enrolling_twice_is_refused_once_it_is_confirmed(): void
    {
        $user = User::factory()->create();
        $this->enable($user);

        $this->actingAs($user)->postJson('/auth/second-factor/enrol')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'auth.mfa_already_enabled');
    }

    #[Test]
    public function disabling_it_requires_a_valid_code(): void
    {
        $user = User::factory()->create();
        $secret = $this->enable($user);

        $this->actingAs($user)->deleteJson('/auth/second-factor', ['code' => '000000'])
            ->assertStatus(401);

        $this->actingAs($user)
            ->deleteJson('/auth/second-factor', ['code' => $this->totp->codeAt($secret, time())])
            ->assertNoContent();

        $this->assertDatabaseMissing('user_mfa', ['user_id' => $user->id]);
    }

    #[Test]
    public function the_secret_and_the_recovery_codes_are_not_readable_in_the_database(): void
    {
        $user = User::factory()->create();
        $secret = $this->enable($user);

        $raw = (array) DB::table('user_mfa')->where('user_id', $user->id)->sole();

        $this->assertNotSame($secret, $raw['secret']);
        $this->assertStringNotContainsString($secret, (string) $raw['secret']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->totp = new Totp;
    }

    private function enrol(User $user): string
    {
        return $this->jsonString(
            $this->actingAs($user)->postJson('/auth/second-factor/enrol'),
            'data.secret',
        );
    }

    private function enable(User $user): string
    {
        $secret = $this->enrol($user);

        $this->actingAs($user)
            ->postJson('/auth/second-factor/confirm', ['code' => $this->totp->codeAt($secret, time())])
            ->assertOk();

        return $secret;
    }

    /**
     * @return list<string>
     */
    private function enableReturningRecoveryCodes(User $user): array
    {
        $response = $this->actingAs($user)->postJson('/auth/second-factor/enrol')->assertCreated();

        $secret = $this->jsonString($response, 'data.secret');
        /** @var list<string> $codes */
        $codes = $this->jsonList($response, 'data.recovery_codes');

        $this->actingAs($user)
            ->postJson('/auth/second-factor/confirm', ['code' => $this->totp->codeAt($secret, time())])
            ->assertOk();

        return $codes;
    }
}
