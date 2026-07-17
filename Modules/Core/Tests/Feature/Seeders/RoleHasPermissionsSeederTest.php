<?php

namespace Modules\Core\Tests\Feature\Seeders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RoleHasPermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\Permission as PermissionEnum;
use Modules\Core\Enums\UserRole;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[CoversClass(RoleHasPermissionsSeeder::class)]
class RoleHasPermissionsSeederTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
    }

    #[Test]
    public function a_role_receives_a_newly_added_default_permission_on_rerun(): void
    {
        /* Arrange */
        $newPermission = Permission::create(['name' => 'view-a-brand-new-thing', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $seeder = new class extends RoleHasPermissionsSeeder {
            protected function getDefaultPermissionsForRole(string $roleName): array
            {
                $permissions = parent::getDefaultPermissionsForRole($roleName);

                if ($roleName === UserRole::ADMIN->value) {
                    $permissions[] = 'view-a-brand-new-thing';
                }

                return $permissions;
            }
        };

        /* Act */
        $seeder->run();

        /* Assert */
        $admin = Role::query()->where('name', UserRole::ADMIN->value)->firstOrFail();
        $this->assertTrue($admin->hasPermissionTo($newPermission));
    }

    #[Test]
    public function custom_permissions_granted_outside_the_seeder_are_not_removed_on_rerun(): void
    {
        /* Arrange */
        $customer = Role::query()->where('name', UserRole::CUSTOMER->value)->firstOrFail();
        $customer->givePermissionTo(PermissionEnum::EXPORT_INVOICES->value);

        /* Act */
        (new RoleHasPermissionsSeeder())->run();

        /* Assert */
        $this->assertTrue($customer->fresh()->hasPermissionTo(PermissionEnum::EXPORT_INVOICES->value));
    }

    #[Test]
    public function super_admin_always_has_every_permission_after_rerun(): void
    {
        /* Arrange */
        Permission::create(['name' => 'some-future-permission', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /* Act */
        (new RoleHasPermissionsSeeder())->run();

        /* Assert */
        $superAdmin = Role::query()->where('name', UserRole::SUPER_ADMIN->value)->firstOrFail();
        $this->assertSame(Permission::query()->count(), $superAdmin->fresh()->permissions->count());
    }
}
