<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Core\Access\AccessChecker;
use App\Modules\Auth\Models\AuthToken;
use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\GrantsAllAccess;
use Tests\TestCase;

final class SuspensionRevokesTokensTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function suspending_a_user_revokes_their_tokens(): void
    {
        $victim = User::factory()->create();
        $token = $this->tokenFor($victim);

        $this->patchJson("/api/users/{$victim->id}", ['status' => 'suspended'])->assertOk();

        $this->assertSame(0, AuthToken::query()->where('user_id', $victim->id)->count());
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/auth/me')->assertStatus(401);
    }

    #[Test]
    public function it_leaves_other_accounts_alone(): void
    {
        $victim = User::factory()->create();
        $bystander = User::factory()->create();

        $this->tokenFor($victim);
        $this->tokenFor($bystander);

        $this->patchJson("/api/users/{$victim->id}", ['status' => 'suspended'])->assertOk();

        $this->assertSame(1, AuthToken::query()->where('user_id', $bystander->id)->count());
    }

    #[Test]
    public function an_unrelated_edit_revokes_nothing(): void
    {
        $user = User::factory()->create();
        $this->tokenFor($user);

        $this->patchJson("/api/users/{$user->id}", ['name' => 'Renamed'])->assertOk();

        $this->assertSame(1, AuthToken::query()->where('user_id', $user->id)->count());
        $this->assertSame(UserStatus::Active, $user->refresh()->status);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance(AccessChecker::class, new GrantsAllAccess);
        $this->actingAs(User::factory()->create());
    }

    private function tokenFor(User $user): string
    {
        return $this->jsonString(
            $this->actingAs($user)->postJson('/auth/tokens', ['name' => 'ci']),
            'data.token',
        );
    }
}
