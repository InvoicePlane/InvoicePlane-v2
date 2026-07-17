<?php

namespace Modules\Core\Tests\Feature\Seeders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\Permission as PermissionEnum;
use Modules\Core\Enums\UserRole;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[CoversClass(RolesSeeder::class)]
class RolesSeederTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles depend on permissions already existing to sync against.
        (new PermissionsSeeder())->run();
    }

    #[Test]
    public function it_creates_all_five_roles(): void
    {
        /* Act */
        (new RolesSeeder())->run();

        /* Assert */
        $roleNames = Role::query()->pluck('name')->toArray();
        foreach ([
            UserRole::SUPER_ADMIN->value,
            UserRole::ADMIN->value,
            UserRole::ASSIST->value,
            UserRole::CUSTOMER_ADMIN->value,
            UserRole::CUSTOMER->value,
        ] as $expectedRole) {
            $this->assertContains($expectedRole, $roleNames);
        }
    }

    #[Test]
    public function super_admin_has_every_permission(): void
    {
        /* Arrange */
        (new RolesSeeder())->run();

        /* Act */
        $role = Role::query()->where('name', UserRole::SUPER_ADMIN->value)->firstOrFail();

        /* Assert */
        $this->assertSame(
            Permission::query()->count(),
            $role->permissions->count()
        );
    }

    #[Test]
    public function admin_has_broad_permissions_but_not_impersonate_backup_or_restore(): void
    {
        /* Arrange */
        (new RolesSeeder())->run();

        /* Act */
        $permissionNames = Role::query()->where('name', UserRole::ADMIN->value)
            ->firstOrFail()
            ->permissions
            ->pluck('name');

        /* Assert */
        $this->assertContains(PermissionEnum::DELETE_INVOICES->value, $permissionNames);
        $this->assertContains(PermissionEnum::MANAGE_ROLES->value, $permissionNames);
        $this->assertNotContains(PermissionEnum::IMPERSONATE_USERS->value, $permissionNames);
        $this->assertNotContains(PermissionEnum::BACKUP->value, $permissionNames);
        $this->assertNotContains(PermissionEnum::RESTORE->value, $permissionNames);
    }

    #[Test]
    public function assist_has_no_delete_manage_approve_or_reject_permissions(): void
    {
        /* Arrange */
        (new RolesSeeder())->run();

        /* Act */
        $permissionNames = Role::query()->where('name', UserRole::ASSIST->value)
            ->firstOrFail()
            ->permissions
            ->pluck('name');

        /* Assert */
        $this->assertContains(PermissionEnum::VIEW_INVOICES->value, $permissionNames);
        $this->assertContains(PermissionEnum::CREATE_INVOICES->value, $permissionNames);

        foreach ($permissionNames as $name) {
            $this->assertStringStartsNotWith('delete-', $name);
            $this->assertStringStartsNotWith('manage-', $name);
            $this->assertStringStartsNotWith('approve-', $name);
            $this->assertStringStartsNotWith('reject-', $name);
        }
        $this->assertNotContains(PermissionEnum::REFUND_PAYMENTS->value, $permissionNames);
    }

    #[Test]
    public function client_admin_has_no_import_permissions(): void
    {
        /* Arrange */
        (new RolesSeeder())->run();

        /* Act */
        $permissionNames = Role::query()->where('name', UserRole::CUSTOMER_ADMIN->value)
            ->firstOrFail()
            ->permissions
            ->pluck('name');

        /* Assert */
        $this->assertContains(PermissionEnum::VIEW_INVOICES->value, $permissionNames);
        $this->assertContains(PermissionEnum::EDIT_INVOICES->value, $permissionNames);

        // client_admin legitimately has delete-* permissions for its own
        // customer-owned resources (relations, invoices, quotes, etc.) --
        // only import- stays fully off-limits.
        foreach ($permissionNames as $name) {
            $this->assertStringStartsNotWith('import-', $name);
        }
    }

    #[Test]
    public function client_has_only_a_minimal_view_and_document_action_allowlist(): void
    {
        /* Arrange */
        (new RolesSeeder())->run();

        /* Act */
        $permissionNames = Role::query()->where('name', UserRole::CUSTOMER->value)
            ->firstOrFail()
            ->permissions
            ->pluck('name');

        /* Assert */
        $this->assertContains(PermissionEnum::VIEW_INVOICES->value, $permissionNames);
        $this->assertContains(PermissionEnum::DOWNLOAD_INVOICES->value, $permissionNames);
        $this->assertContains(PermissionEnum::PRINT_INVOICES->value, $permissionNames);
        $this->assertContains(PermissionEnum::VIEW_QUOTES->value, $permissionNames);
        $this->assertContains(PermissionEnum::VIEW_PAYMENTS->value, $permissionNames);

        $this->assertNotContains(PermissionEnum::CREATE_INVOICES->value, $permissionNames);
        $this->assertNotContains(PermissionEnum::DELETE_INVOICES->value, $permissionNames);
        $this->assertNotContains(PermissionEnum::EDIT_PAYMENTS->value, $permissionNames);
    }
}
