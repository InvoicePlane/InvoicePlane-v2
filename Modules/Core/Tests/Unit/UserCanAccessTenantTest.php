<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class UserCanAccessTenantTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_denies_access_to_a_different_company(): void
    {
        /* Arrange */
        $otherCompany = Company::factory()->create();

        /* Act */
        $result = $this->user->canAccessTenant($otherCompany);

        /* Assert */
        $this->assertFalse($result);
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_user_to_access_their_own_company(): void
    {
        /* Arrange */
        // $this->user is already attached to $this->company via AbstractCompanyPanelTestCase

        /* Act */
        $result = $this->user->canAccessTenant($this->company);

        /* Assert */
        $this->assertTrue($result);
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_superadmin_to_access_any_tenant(): void
    {
        /* Arrange */
        Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');
        $unrelatedCompany = Company::factory()->create();

        /* Act */
        $result = $superAdmin->canAccessTenant($unrelatedCompany);

        /* Assert */
        $this->assertTrue($result);
    }

    #[Test]
    #[Group('unit')]
    public function it_denies_access_to_a_user_with_no_company_association(): void
    {
        /* Arrange */
        $userWithNoCompany = User::factory()->create();

        /* Act */
        $result = $userWithNoCompany->canAccessTenant($this->company);

        /* Assert */
        $this->assertFalse($result);
    }
}
