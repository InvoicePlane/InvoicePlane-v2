<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Enums\UserRole;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class CompanyPanelAdminLinkTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_shows_the_admin_panel_link_to_an_elevated_user(): void
    {
        /* Arrange */
        Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
        $this->user->assignRole(UserRole::SUPER_ADMIN->value);

        /* Act */
        $response = $this->actingAs($this->user)->get(
            route('filament.company.pages.dashboard', ['tenant' => 'IVPLV2'])
        );

        /* Assert */
        $response->assertSuccessful();
        $response->assertSee('Admin Panel');
    }

    #[Test]
    public function it_hides_the_admin_panel_link_from_a_non_elevated_user(): void
    {
        /* Arrange */
        Role::query()->firstOrCreate(['name' => UserRole::CUSTOMER->value, 'guard_name' => 'web']);
        $this->user->assignRole(UserRole::CUSTOMER->value);

        /* Act */
        $response = $this->actingAs($this->user)->get(
            route('filament.company.pages.dashboard', ['tenant' => 'IVPLV2'])
        );

        /* Assert */
        $response->assertSuccessful();
        $response->assertDontSee('Admin Panel');
    }
}
