<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class WebRoutesTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    #[Test]
    #[Group('unit')]
    public function it_redirects_root_to_dashboard_with_current_company(): void
    {
        $this->markTestIncomplete();

        $user    = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        // Set current company in session
        Session::put('current_company_id', $company->id);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('filament.company.pages.dashboard', ['company' => $company]));
    }

    #[Test]
    #[Group('unit')]
    public function it_redirects_dashboard_to_dashboard_with_current_company(): void
    {
        $this->markTestIncomplete();

        $user    = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        // Set current company in session
        Session::put('current_company_id', $company->id);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('filament.company.pages.dashboard', ['company' => $company]));
    }

    #[Test]
    #[Group('unit')]
    public function it_falls_back_to_first_company_if_no_current_company_in_session(): void
    {
        $this->markTestIncomplete();

        $user    = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        // Don't set current_company_id in session

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('filament.company.pages.dashboard', ['company' => $company]));
    }

    #[Test]
    #[Group('unit')]
    public function it_returns_404_if_user_has_no_companies(): void
    {
        $this->markTestIncomplete();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(404);
    }

    #[Test]
    #[Group('unit')]
    public function it_handles_invalid_company_id_in_session(): void
    {
        $this->markTestIncomplete();

        $user    = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company);

        // Set invalid company ID in session
        Session::put('current_company_id', 9999);

        $response = $this->actingAs($user)->get('/');

        // Should fall back to first company
        $response->assertRedirect(route('filament.company.pages.dashboard', ['company' => $company]));
    }
}
