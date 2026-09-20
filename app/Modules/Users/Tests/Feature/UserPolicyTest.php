<?php

declare(strict_types=1);

namespace App\Modules\Users\Tests\Feature;

use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_module_policy_is_registered(): void
    {
        $this->assertNotNull(Gate::getPolicyFor(User::class));
    }

    #[Test]
    public function a_user_may_read_and_edit_itself(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('view', $user));
        $this->assertTrue(Gate::forUser($user)->allows('update', $user));
    }

    #[Test]
    public function a_user_may_not_edit_someone_else(): void
    {
        $ada = User::factory()->create();
        $grace = User::factory()->create();

        $this->assertFalse(Gate::forUser($ada)->allows('view', $grace));
        $this->assertFalse(Gate::forUser($ada)->allows('update', $grace));
    }

    #[Test]
    public function nobody_deletes_a_user_until_roles_exist(): void
    {
        $ada = User::factory()->create();

        $this->assertFalse(Gate::forUser($ada)->allows('delete', $ada));
    }

    #[Test]
    public function a_suspended_user_may_not_browse(): void
    {
        $suspended = User::factory()->suspended()->create();

        $this->assertFalse(Gate::forUser($suspended)->allows('viewAny', User::class));
    }
}
