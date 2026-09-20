<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use App\Core\Audit\AuditLog;
use App\Core\Audit\AuditTrail;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_records_the_acting_user(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor);

        $this->app->make(AuditTrail::class)->record('users.suspended', 'user', 42, ['reason' => 'abuse']);

        $entry = AuditLog::query()->sole();

        $this->assertSame($actor->id, $entry->actor_id);
        $this->assertSame('users.suspended', $entry->action);
        $this->assertSame('user', $entry->subject_type);
        $this->assertSame('42', $entry->subject_id);
        $this->assertSame(['reason' => 'abuse'], $entry->context);
    }

    #[Test]
    public function it_records_a_system_action_with_no_actor(): void
    {
        $this->app->make(AuditTrail::class)->record('system.started');

        $entry = AuditLog::query()->sole();

        $this->assertNull($entry->actor_id);
        $this->assertNull($entry->subject_type);
        $this->assertNotNull($entry->created_at);
    }

    #[Test]
    public function it_survives_the_deletion_of_its_actor(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor);

        $this->app->make(AuditTrail::class)->record('users.created', 'user', $actor->id);

        $actor->delete();

        $this->assertSame(1, AuditLog::query()->count());
    }
}
