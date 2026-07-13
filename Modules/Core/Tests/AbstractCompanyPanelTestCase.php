<?php

namespace Modules\Core\Tests;

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

abstract class AbstractCompanyPanelTestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected User $user;

    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:00'));

        /** @var User $user */
        $user = User::factory()->withCompany([
            'search_code' => 'IVPLV2',
            'name'        => 'InvoicePlane Corporation',
            'slug'        => 'invoiceplane-corporation',
        ])->create();
        $this->user = $user;

        /** @var Company $company */
        $company       = Company::query()->where('search_code', 'IVPLV2')->firstOrFail();
        $this->company = $company;

        /*
         * quietly set tenant so it won't wine about user not being set yet.
         */
        Filament::setTenant($this->company, true);

        $currentCompanyId = $this->user->getCurrentCompanyId();
        session(['current_company_id' => $currentCompanyId]);

        $this->withoutExceptionHandling();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Clear the test time
        parent::tearDown();
    }

    /**
     * Test a Livewire component with the current company session.
     */
    protected function testLivewire($component, $params = [])
    {
        session(['current_company_id' => $this->company->id]);

        return Livewire::actingAs($this->user)
            ->test($component, $params);
    }
}
