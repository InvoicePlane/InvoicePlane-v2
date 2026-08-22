<?php

namespace Modules\Invoices\Tests\Feature;

use Filament\Facades\Filament;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * End-to-end check for #382: an invoice created through the real Filament
 * flow keeps its original company name after the company is renamed through
 * the real Admin panel edit flow — not just via direct service/model calls.
 */
#[Group('crud')]
class CompanyRenameInvoicePreviewTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function editing_the_company_through_the_admin_panel_does_not_change_an_already_issued_invoices_preview(): void
    {
        /* Arrange */
        $this->company->update(['name' => 'Old Company Name BV']);

        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'invoice_number' => 'INV-CRP-001',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => 'sent',
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
            'invoiceItems'   => [
                [
                    'product_id' => $product->getKey(),
                    'quantity'   => 3,
                    'price'      => 150,
                    'discount'   => 0,
                ],
            ],
        ];

        /* Act — create the invoice through the real Filament "create" modal */
        Livewire::actingAs($this->user)->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->assertHasNoFormErrors()
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $invoice = Invoice::query()->where('invoice_number', 'INV-CRP-001')->firstOrFail();
        $this->assertSame('Old Company Name BV', $invoice->company_name);

        /* Act — rename the company through the real Admin panel edit action */
        $this->editCompanyNameThroughAdminPanel($this->company, 'New Company Name NV');
        $this->assertSame('New Company Name NV', $this->company->fresh()->name);

        /* Assert — the already-issued invoice's preview still shows the old name */
        Filament::setCurrentPanel(Filament::getPanel('company'));
        Filament::setTenant($this->company, true);
        session(['current_company_id' => $this->company->id]);

        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->mountAction('preview')
            ->assertMountedActionModalSee('Old Company Name BV', escape: false)
            ->assertMountedActionModalDontSee('New Company Name NV', escape: false);
    }

    /**
     * Drives the real Admin > Companies > edit modal action (there is no
     * dedicated edit page, it's a table row action — see CompaniesTable),
     * switching into the admin panel as a super admin, then switching back.
     */
    private function editCompanyNameThroughAdminPanel(Company $company, string $newName): void
    {
        /** @var User $superAdmin */
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $formData = $company->only(['search_code', 'name', 'slug', 'vat_number', 'id_number', 'coc_number']);
        $formData['name'] = $newName;

        Livewire::actingAs($superAdmin)
            ->test(\Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies::class)
            ->callTableAction('edit', $company, $formData)
            ->assertHasNoTableActionErrors();
    }
}
