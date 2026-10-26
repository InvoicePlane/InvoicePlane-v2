<?php

namespace Modules\Core\Tests\Feature\Seeders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Enums\Permission as PermissionEnum;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

#[CoversClass(PermissionsSeeder::class)]
class PermissionsSeederTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_every_permission_enum_case_in_the_database(): void
    {
        /* Arrange */
        $expected = array_column(PermissionEnum::cases(), 'value');

        /* Act */
        (new PermissionsSeeder())->run();

        /* Assert */
        $created = Permission::query()->pluck('name')->toArray();
        foreach ($expected as $permissionName) {
            $this->assertContains($permissionName, $created);
        }
    }

    #[Test]
    public function it_does_not_create_duplicate_permissions_when_run_twice(): void
    {
        /* Arrange */
        $expectedCount = count(array_column(PermissionEnum::cases(), 'value'));

        /* Act */
        (new PermissionsSeeder())->run();
        (new PermissionsSeeder())->run();

        /* Assert */
        $this->assertSame(
            $expectedCount,
            Permission::query()->whereIn('name', array_column(PermissionEnum::cases(), 'value'))->count()
        );
    }

    #[Test]
    public function it_leaves_pre_existing_permissions_from_a_prior_run_untouched(): void
    {
        /* Arrange */
        (new PermissionsSeeder())->run();
        $originalId = Permission::query()->where('name', PermissionEnum::VIEW_INVOICES->value)->firstOrFail()->id;

        /* Act */
        (new PermissionsSeeder())->run();

        /* Assert */
        $this->assertSame(
            $originalId,
            Permission::query()->where('name', PermissionEnum::VIEW_INVOICES->value)->firstOrFail()->id
        );
    }
}
