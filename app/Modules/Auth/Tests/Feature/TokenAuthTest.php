<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Models\AuthToken;
use App\Modules\Auth\Services\TokenMint;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TokenAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_issues_a_token_and_returns_the_plaintext_once(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/auth/tokens', ['name' => 'ci-runner'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'ci-runner');

        $plainText = $this->jsonString($response, 'data.token');

        $this->assertStringStartsWith(TokenMint::PREFIX, $plainText);

        // Listing it again never shows the value.
        $this->actingAs($user)->getJson('/auth/tokens')
            ->assertOk()
            ->assertJsonMissing(['token' => $plainText]);
    }

    #[Test]
    public function only_the_hash_reaches_the_database(): void
    {
        $user = User::factory()->create();

        $plainText = $this->jsonString(
            $this->actingAs($user)->postJson('/auth/tokens', ['name' => 'ci-runner']),
            'data.token',
        );

        $stored = DB::table('auth_tokens')->where('user_id', $user->id)->sole();

        $this->assertNotSame($plainText, $stored->token_hash);
        $this->assertSame((new TokenMint)->hash($plainText), $stored->token_hash);
    }

    #[Test]
    public function the_token_authenticates_a_stateless_request(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace']);
        $plainText = $this->issue($user);

        $this->withToken($plainText)->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Ada Lovelace');
    }

    #[Test]
    public function an_unknown_or_malformed_token_is_rejected(): void
    {
        $this->withToken('mod_nonsense')->getJson('/api/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'core.unauthenticated');

        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    #[Test]
    public function an_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $plainText = $this->jsonString(
            $this->actingAs($user)->postJson('/auth/tokens', ['name' => 'short-lived', 'expires_in_days' => 1]),
            'data.token',
        );

        $this->withToken($plainText)->getJson('/api/auth/me')->assertOk();

        Carbon::setTestNow(Carbon::now()->addDays(2));

        // Guards memoise; one test shares a container where production would not.
        $this->app['auth']->forgetGuards();

        $this->withToken($plainText)->getJson('/api/auth/me')->assertStatus(401);

        Carbon::setTestNow();
    }

    #[Test]
    public function using_a_token_records_when_it_was_last_used(): void
    {
        $user = User::factory()->create();
        $plainText = $this->issue($user);

        $this->assertNull(AuthToken::query()->sole()->last_used_at);

        $this->withToken($plainText)->getJson('/api/auth/me')->assertOk();

        $this->assertNotNull(AuthToken::query()->sole()->last_used_at);
    }

    #[Test]
    public function a_user_can_revoke_their_own_token(): void
    {
        $user = User::factory()->create();
        $plainText = $this->issue($user);
        $id = AuthToken::query()->sole()->id;

        $this->actingAs($user)->deleteJson("/auth/tokens/{$id}")->assertNoContent();

        $this->withToken($plainText)->getJson('/api/auth/me')->assertStatus(401);
    }

    #[Test]
    public function one_user_cannot_revoke_another_users_token(): void
    {
        $ada = User::factory()->create();
        $grace = User::factory()->create();

        $this->issue($grace);
        $id = AuthToken::query()->sole()->id;

        // A 404, not a 403: the caller should not learn that the id exists.
        $this->actingAs($ada)->deleteJson("/auth/tokens/{$id}")
            ->assertNotFound()
            ->assertJsonPath('error.code', 'auth_tokens.not_found');

        $this->assertDatabaseHas('auth_tokens', ['id' => $id]);
    }

    #[Test]
    public function a_guest_cannot_mint_a_token(): void
    {
        $this->postJson('/auth/tokens', ['name' => 'nope'])->assertStatus(401);
    }

    #[Test]
    public function it_validates_the_token_payload(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/auth/tokens', ['expires_in_days' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'expires_in_days']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function issue(User $user): string
    {
        return $this->jsonString(
            $this->actingAs($user)->postJson('/auth/tokens', ['name' => 'ci-runner']),
            'data.token',
        );
    }
}
