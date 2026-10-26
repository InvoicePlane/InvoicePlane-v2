<?php

namespace Modules\Core\Tests\Feature\Admin;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies;
use Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions\AssignInvoiceGroupBulkAction;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class CompanyInvoiceGroupBulkActionTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_assigns_invoice_group_to_multiple_companies(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(2)->create();
        $numbering = Numbering::factory()->create();
        $ids       = $companies->pluck('id')->all();

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($ids)
            ->callAction(TestAction::make('assignInvoiceGroup')->table()->bulk(), ['numbering_id' => $numbering->id])
            ->assertHasNoFormErrors();

        /* Assert */
        foreach ($companies as $company) {
            $this->assertDatabaseHas('company_numbering', [
                'company_id'   => $company->id,
                'numbering_id' => $numbering->id,
            ]);
        }
    }

    #[Test]
    public function it_does_not_duplicate_the_pivot_row_when_the_invoice_group_is_already_assigned(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->create();
        $company->invoiceGroups()->attach($numbering->id);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignInvoiceGroup')->table()->bulk(), ['numbering_id' => $numbering->id])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_numbering', 1);
    }

    #[Test]
    public function it_loads_an_invoice_group_owned_by_another_company_through_the_pivot(): void
    {
        /* Arrange */
        $ownerCompany  = Company::factory()->create();
        $targetCompany = Company::factory()->create();
        $numbering     = Numbering::factory()->for($ownerCompany)->create();
        $targetCompany->invoiceGroups()->attach($numbering->id);

        /* Act */
        $loaded = $targetCompany->invoiceGroups()->find($numbering->id);

        /* Assert */
        $this->assertNotNull($loaded);
        $this->assertSame($numbering->id, $loaded->id);
    }

    #[Test]
    public function it_shows_titles_disambiguated_by_owning_company(): void
    {
        /* Arrange */
        $companyOne = Company::factory()->create(['name' => 'Acme Corp']);
        $companyTwo = Company::factory()->create(['name' => 'Other Corp']);
        $numberingOne = Numbering::factory()->for($companyOne)->create(['name' => 'Standard Invoices']);
        $numberingTwo = Numbering::factory()->for($companyTwo)->create(['name' => 'Standard Invoices']);

        /* Act */
        $options = AssignInvoiceGroupBulkAction::getNumberingOptions();

        /* Assert */
        $this->assertSame('Standard Invoices (Acme Corp)', $options[$numberingOne->id]);
        $this->assertSame('Standard Invoices (Other Corp)', $options[$numberingTwo->id]);
        $this->assertNotSame($options[$numberingOne->id], $options[$numberingTwo->id]);
    }

    #[Test]
    public function it_requires_an_invoice_group_to_be_selected(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act & Assert */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignInvoiceGroup')->table()->bulk(), ['numbering_id' => null])
            ->assertHasFormErrors(['numbering_id' => 'required']);

        $this->assertDatabaseCount('company_numbering', 0);
    }

    #[Test]
    public function it_assigns_the_invoice_group_to_every_selected_company_in_one_bulk_call(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(3)->create();
        $numbering = Numbering::factory()->create();

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($companies->pluck('id')->all())
            ->callAction(TestAction::make('assignInvoiceGroup')->table()->bulk(), ['numbering_id' => $numbering->id])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_numbering', 3);
    }
}
