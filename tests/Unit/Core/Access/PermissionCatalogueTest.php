<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Access;

use App\Core\Access\DuplicatePermission;
use App\Core\Access\PermissionCatalogue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PermissionCatalogueTest extends TestCase
{
    #[Test]
    public function it_collects_what_modules_declare(): void
    {
        $catalogue = new PermissionCatalogue;

        $catalogue->declare('Users', ['users.view' => 'View users']);
        $catalogue->declare('Access', ['access.assign' => 'Assign roles']);

        $this->assertSame(['access.assign', 'users.view'], $catalogue->names());
        $this->assertTrue($catalogue->has('users.view'));
        $this->assertFalse($catalogue->has('users.invent'));
    }

    #[Test]
    public function it_remembers_which_module_owns_a_permission(): void
    {
        $catalogue = new PermissionCatalogue;
        $catalogue->declare('Users', ['users.view' => 'View users']);

        $this->assertSame('Users', $catalogue->moduleFor('users.view'));
        $this->assertNull($catalogue->moduleFor('nothing.here'));
    }

    #[Test]
    public function it_refuses_the_same_permission_from_two_modules(): void
    {
        $catalogue = new PermissionCatalogue;
        $catalogue->declare('Users', ['shared.thing' => 'One']);

        $this->expectException(DuplicatePermission::class);
        $this->expectExceptionMessage('declared by both');

        $catalogue->declare('Access', ['shared.thing' => 'Two']);
    }

    #[Test]
    public function a_module_may_redeclare_its_own_permission(): void
    {
        $catalogue = new PermissionCatalogue;

        $catalogue->declare('Users', ['users.view' => 'View users']);
        $catalogue->declare('Users', ['users.view' => 'Browse users']);

        $this->assertSame(['users.view' => 'Browse users'], $catalogue->all());
    }

    #[Test]
    public function it_groups_by_module_for_a_permissions_screen(): void
    {
        $catalogue = new PermissionCatalogue;
        $catalogue->declare('Users', ['users.view' => 'View users', 'users.create' => 'Create users']);
        $catalogue->declare('Access', ['access.assign' => 'Assign roles']);

        $this->assertSame(
            [
                'Access' => ['access.assign' => 'Assign roles'],
                'Users' => ['users.create' => 'Create users', 'users.view' => 'View users'],
            ],
            $catalogue->groupedByModule(),
        );
    }
}
