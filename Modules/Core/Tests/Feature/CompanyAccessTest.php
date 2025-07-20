<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;

class CompanyAccessTest extends AbstractTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Seed roles/permissions if needed
    }

    public function test_elevated_roles_can_access_any_company(): void
    {
        $this->markTestIncomplete();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $roles    = UserRole::elevated();

        foreach ($roles as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);
            $this->actingAs($user);

            $response = $this->get(route('filament.company.pages.dashboard', ['tenant' => $companyB->search_code]));
            $response->assertStatus(200);
        }
    }

    public function test_non_elevated_user_cannot_access_other_companies(): void
    {
        $this->markTestIncomplete();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user     = User::factory()->create();
        $user->assignRole(UserRole::CUSTOMER->value);
        $user->companies()->attach($companyA);
        $this->actingAs($user);

        $response = $this->get(route('filament.company.pages.dashboard', ['tenant' => $companyB->search_code]));
        $response->assertStatus(403);
    }

    public function test_non_elevated_user_can_access_own_company(): void
    {
        $this->markTestIncomplete();

        $companyA = Company::factory()->create();
        $user     = User::factory()->create();
        $user->assignRole(UserRole::CUSTOMER->value);
        $user->companies()->attach($companyA);
        $this->actingAs($user);

        $response = $this->get(route('filament.company.pages.dashboard', ['tenant' => $companyA->search_code]));
        $response->assertStatus(200);
    }
}
