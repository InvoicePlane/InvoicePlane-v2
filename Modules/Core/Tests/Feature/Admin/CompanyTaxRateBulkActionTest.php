<?php

namespace Modules\Core\Tests\Feature\Admin;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies;
use Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions\AssignTaxRateBulkAction;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class CompanyTaxRateBulkActionTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_assigns_tax_rate_to_multiple_companies(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(2)->create();
        $taxRate   = TaxRate::factory()->create();
        $ids       = $companies->pluck('id')->all();

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($ids)
            ->callAction(TestAction::make('assignTaxRate')->table()->bulk(), ['tax_rate_id' => $taxRate->id])
            ->assertHasNoFormErrors();

        /* Assert */
        foreach ($companies as $company) {
            $this->assertDatabaseHas('company_tax_rate', [
                'company_id'  => $company->id,
                'tax_rate_id' => $taxRate->id,
            ]);
        }
    }

    #[Test]
    public function it_does_not_duplicate_the_pivot_row_when_the_tax_rate_is_already_assigned(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        $taxRate = TaxRate::factory()->create();
        $company->assignedTaxRates()->attach($taxRate->id);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignTaxRate')->table()->bulk(), ['tax_rate_id' => $taxRate->id])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_tax_rate', 1);
    }

    #[Test]
    public function it_loads_a_tax_rate_owned_by_another_company_through_the_pivot(): void
    {
        /* Arrange */
        $ownerCompany  = Company::factory()->create();
        $targetCompany = Company::factory()->create();
        $taxRate       = TaxRate::factory()->for($ownerCompany)->create();
        $targetCompany->assignedTaxRates()->attach($taxRate->id);

        /* Act */
        $loaded = $targetCompany->assignedTaxRates()->find($taxRate->id);

        /* Assert */
        $this->assertNotNull($loaded);
        $this->assertSame($taxRate->id, $loaded->id);
    }

    #[Test]
    public function it_shows_names_disambiguated_by_owning_company(): void
    {
        /* Arrange */
        $companyOne = Company::factory()->create(['name' => 'Acme Corp']);
        $companyTwo = Company::factory()->create(['name' => 'Other Corp']);
        $taxRateOne = TaxRate::factory()->for($companyOne)->create(['name' => 'VAT Standard']);
        $taxRateTwo = TaxRate::factory()->for($companyTwo)->create(['name' => 'VAT Standard']);

        /* Act */
        $options = AssignTaxRateBulkAction::getTaxRateOptions();

        /* Assert */
        $this->assertSame('VAT Standard (Acme Corp)', $options[$taxRateOne->id]);
        $this->assertSame('VAT Standard (Other Corp)', $options[$taxRateTwo->id]);
        $this->assertNotSame($options[$taxRateOne->id], $options[$taxRateTwo->id]);
    }

    #[Test]
    public function it_requires_a_tax_rate_to_be_selected(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act & Assert */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignTaxRate')->table()->bulk(), ['tax_rate_id' => null])
            ->assertHasFormErrors(['tax_rate_id' => 'required']);

        $this->assertDatabaseCount('company_tax_rate', 0);
    }

    #[Test]
    public function it_assigns_the_tax_rate_to_every_selected_company_in_one_bulk_call(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(3)->create();
        $taxRate   = TaxRate::factory()->create();

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($companies->pluck('id')->all())
            ->callAction(TestAction::make('assignTaxRate')->table()->bulk(), ['tax_rate_id' => $taxRate->id])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_tax_rate', 3);
    }
}
