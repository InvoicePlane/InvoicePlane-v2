<?php

namespace Modules\Core\Tests;

use Filament\Facades\Filament;
use Filament\Schemas\Components\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

abstract class AbstractCompanyPanelTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $company    = Company::factory()->create([
            'search_code' => 'IVPLV2',
            'name'        => 'InvoicePlane Corporation',
            'slug'        => 'invoiceplane-corporation',
        ]);
        $this->user->companies()->attach($company);
        $this->company = Company::query()->where('search_code', 'IVPLV2')->first();

        /*
         * quietly set tenant so it won't whine about user not being set yet.
         */
        Filament::setTenant($this->company, true);

        $currentCompanyId = $this->user->getCurrentCompanyId();
        session(['current_company_id' => $currentCompanyId]);

        $this->withoutExceptionHandling();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test a Livewire component with the current company session.
     */
    protected function testLivewire($component, $params = [])
    {
        return Livewire::actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->test($component, $params);
    }
}
