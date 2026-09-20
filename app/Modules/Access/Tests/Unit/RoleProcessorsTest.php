<?php

declare(strict_types=1);

namespace App\Modules\Access\Tests\Unit;

use App\Core\Access\PermissionCatalogue;
use App\Modules\Access\Data\PermissionSync;
use App\Modules\Access\Data\RoleData;
use App\Modules\Access\Exceptions\RoleNameTaken;
use App\Modules\Access\Exceptions\SystemRoleIsProtected;
use App\Modules\Access\Exceptions\UnknownPermission;
use App\Modules\Access\Processors\CreateRole;
use App\Modules\Access\Processors\DeleteRole;
use App\Modules\Access\Processors\SyncRolePermissions;
use App\Modules\Access\Processors\UpdateRole;
use App\Modules\Access\Tests\Support\InMemoryRoleRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\RecordingAuditTrail;

final class RoleProcessorsTest extends TestCase
{
    #[Test]
    public function it_creates_a_role_and_audits_it(): void
    {
        $roles = new InMemoryRoleRepository;
        $audit = new RecordingAuditTrail;

        $role = (new CreateRole($roles, $audit))->process(new RoleData('support', 'Support'));

        $this->assertSame('support', $role->name);
        $this->assertSame('access.role.created', $audit->entries[0]['action']);
        $this->assertSame(['name' => 'support'], $audit->entries[0]['context']);
    }

    #[Test]
    public function it_refuses_a_duplicate_name(): void
    {
        $roles = new InMemoryRoleRepository;
        $processor = new CreateRole($roles, new RecordingAuditTrail);

        $processor->process(new RoleData('support', 'Support'));

        $this->expectException(RoleNameTaken::class);

        $processor->process(new RoleData('support', 'Support again'));
    }

    #[Test]
    public function it_will_not_rename_a_system_role(): void
    {
        $roles = new InMemoryRoleRepository;
        $system = $roles->createSystem('administrator', 'Administrator');

        $this->expectException(SystemRoleIsProtected::class);

        (new UpdateRole($roles, new RecordingAuditTrail))->process([
            'id' => $system->id,
            'data' => new RoleData('renamed', 'Renamed'),
        ]);
    }

    #[Test]
    public function it_will_not_delete_a_system_role(): void
    {
        $roles = new InMemoryRoleRepository;
        $system = $roles->createSystem('administrator', 'Administrator');

        $this->expectException(SystemRoleIsProtected::class);

        (new DeleteRole($roles, new RecordingAuditTrail))->process($system->id);
    }

    #[Test]
    public function it_refuses_a_permission_the_catalogue_does_not_know(): void
    {
        $roles = new InMemoryRoleRepository;
        $role = $roles->create(new RoleData('support', 'Support'));

        $catalogue = new PermissionCatalogue;
        $catalogue->declare('Users', ['users.view' => 'View users']);

        $this->expectException(UnknownPermission::class);

        (new SyncRolePermissions($roles, $catalogue, new RecordingAuditTrail))->process(
            new PermissionSync($role->id, ['users.view', 'users.invent']),
        );
    }

    #[Test]
    public function it_syncs_permissions_the_catalogue_knows(): void
    {
        $roles = new InMemoryRoleRepository;
        $role = $roles->create(new RoleData('support', 'Support'));
        $audit = new RecordingAuditTrail;

        $catalogue = new PermissionCatalogue;
        $catalogue->declare('Users', ['users.view' => 'View users']);

        (new SyncRolePermissions($roles, $catalogue, $audit))->process(
            new PermissionSync($role->id, ['users.view']),
        );

        $this->assertSame(['users.view'], $roles->permissionsOf($role->id));
        $this->assertSame('access.role.permissions_synced', $audit->entries[0]['action']);
    }
}
