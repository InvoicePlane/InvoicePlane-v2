<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

abstract class AbstractAdminPanelTestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected $company;

    protected ?User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:00'));

        filament()->setCurrentPanel(filament()->getPanel('admin'));

        /** @var Company $company */
        $company       = Company::factory()->create();
        $this->company = $company;

        /** @var User $superAdmin */
        $superAdmin       = User::factory()->create();
        $this->superAdmin = $superAdmin;

        session(['current_company_id' => $this->company->id]);

        /*
         * Admin resources gate every page on Spatie permissions (canViewAny
         * etc.), so the test user needs the seeded super_admin permission set.
         */
        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
        $this->superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

        $this->withoutExceptionHandling();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Clear the test time
        parent::tearDown();
    }

    protected function superAdmin(): User
    {
        return $this->superAdmin;
    }
}
