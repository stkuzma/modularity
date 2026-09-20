<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Tests\Feature;

use App\Core\Access\AccessChecker;
use App\Modules\Invitations\Events\InvitationAccepted;
use App\Modules\Invitations\Models\Invitation;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class InvitationsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_invites_and_only_the_hash_is_stored(): void
    {
        $response = $this->postJson('/api/invitations', ['email' => 'Ada@Example.com'])
            ->assertCreated()
            ->assertJsonPath('data.email', 'ada@example.com');

        $url = $this->jsonString($response, 'data.accept_url');
        $token = basename(parse_url($url, PHP_URL_PATH) ?: '');

        $stored = DB::table('invitations')->where('email', 'ada@example.com')->sole();

        $this->assertNotSame($token, $stored->token_hash);
        $this->assertSame(hash('sha256', $token), $stored->token_hash);
    }

    #[Test]
    public function it_refuses_an_address_that_already_has_an_account_or_an_invitation(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/invitations', ['email' => 'taken@example.com'])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'invitations.already_invited');

        $this->postJson('/api/invitations', ['email' => 'ada@example.com'])->assertCreated();
        $this->postJson('/api/invitations', ['email' => 'ada@example.com'])->assertStatus(409);
    }

    #[Test]
    public function accepting_creates_the_account_through_the_users_contract(): void
    {
        Event::fake([InvitationAccepted::class]);

        $token = $this->invite('ada@example.com');

        $this->post(route('invitations.accept.submit'), [
            'token' => $token,
            'name' => 'Ada Lovelace',
            'password' => 'secret-password',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com', 'name' => 'Ada Lovelace']);
        $this->assertNotNull(Invitation::query()->sole()->accepted_at);

        Event::assertDispatched(InvitationAccepted::class);
    }

    #[Test]
    public function the_new_account_can_sign_in(): void
    {
        // Skipped rather than asserted when the module that owns sign-in is
        // not installed: this module's tests must not require it.
        if (! Route::has('sign-in.submit')) {
            $this->markTestSkipped('The Auth module is not installed.');
        }

        $token = $this->invite('ada@example.com');

        $this->post(route('invitations.accept.submit'), [
            'token' => $token,
            'name' => 'Ada',
            'password' => 'secret-password',
        ]);

        $this->post(route('sign-in.submit'), ['email' => 'ada@example.com', 'password' => 'secret-password'])
            ->assertRedirect(route('dashboard'));
    }

    #[Test]
    public function an_invitation_cannot_be_used_twice(): void
    {
        $token = $this->invite('ada@example.com');
        $payload = ['token' => $token, 'name' => 'Ada', 'password' => 'secret-password'];

        $this->post(route('invitations.accept.submit'), $payload);

        $this->from(route('invitations.accept', ['token' => $token]))
            ->post(route('invitations.accept.submit'), $payload)
            ->assertSessionHas('error');

        $this->assertSame(1, User::query()->where('email', 'ada@example.com')->count());
    }

    #[Test]
    public function an_expired_invitation_is_refused(): void
    {
        $token = $this->invite('ada@example.com');

        Carbon::setTestNow(Carbon::now()->addDays(8));

        $this->from(route('invitations.accept', ['token' => $token]))
            ->post(route('invitations.accept.submit'), [
                'token' => $token,
                'name' => 'Ada',
                'password' => 'secret-password',
            ])
            ->assertSessionHas('error');

        Carbon::setTestNow();

        $this->assertDatabaseMissing('users', ['email' => 'ada@example.com']);
    }

    #[Test]
    public function an_unknown_token_is_a_404(): void
    {
        $this->postJson('/api/invitations');

        $this->postJson('/invite', ['token' => 'nonsense', 'name' => 'X', 'password' => 'secret-password'])
            ->assertNotFound()
            ->assertJsonPath('error.code', 'invitations.not_found');
    }

    #[Test]
    public function the_screen_lists_and_revokes(): void
    {
        $this->invite('ada@example.com');

        $this->get(route('invitations.index'))->assertOk()->assertSee('ada@example.com');

        $id = Invitation::query()->sole()->id;

        $this->delete(route('invitations.destroy', $id))
            ->assertRedirect(route('invitations.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('invitations', ['id' => $id]);
    }

    #[Test]
    public function the_form_hands_back_the_link_once(): void
    {
        $this->post(route('invitations.store'), ['email' => 'ada@example.com'])
            ->assertRedirect(route('invitations.index'))
            ->assertSessionHas('invite_url');

        $this->get(route('invitations.index'))->assertOk()->assertSee('ada@example.com');

        // Flashed, so a reload does not show it again.
        $this->get(route('invitations.index'))->assertOk()->assertDontSee('/invite/');
    }

    #[Test]
    public function revoking_an_accepted_invitation_is_refused(): void
    {
        $token = $this->invite('ada@example.com');

        $this->post(route('invitations.accept.submit'), [
            'token' => $token,
            'name' => 'Ada',
            'password' => 'secret-password',
        ]);

        $id = Invitation::query()->sole()->id;

        $this->deleteJson("/api/invitations/{$id}")
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'invitations.already_accepted');
    }

    #[Test]
    public function a_caller_who_may_only_view_cannot_invite(): void
    {
        $this->app->instance(AccessChecker::class, new GrantsAllAccess(['invitations.view']));

        $this->getJson('/api/invitations')->assertOk();

        $this->postJson('/api/invitations', ['email' => 'ada@example.com'])
            ->assertForbidden()
            ->assertJsonPath('error.details.permission', 'invitations.send');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance(AccessChecker::class, new GrantsAllAccess);
        $this->actingAs(User::factory()->create());
    }

    private function invite(string $email): string
    {
        $url = $this->jsonString($this->postJson('/api/invitations', ['email' => $email]), 'data.accept_url');

        return basename(parse_url($url, PHP_URL_PATH) ?: '');
    }
}
