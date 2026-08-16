<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Test;

class InvoiceTemplateSelectionTest extends AbstractCompanyPanelTestCase
{
    protected ReportTemplateStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ReportTemplateStorage::DISK);

        $this->storage = new ReportTemplateStorage();

        $this->artisan('reports:sync-system');
    }

    #[Test]
    public function it_lists_disk_templates_as_options_for_the_invoice_type(): void
    {
        /* Arrange */
        $this->storage->clone('system', 'default', 'Fancy', ReportTemplateType::INVOICE);

        /* Act */
        $options = $this->storage->optionsForType(ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertSame(['default' => 'Default Invoice', 'fancy' => 'Fancy'], $options);
    }

    #[Test]
    public function it_does_not_offer_quote_templates_for_invoices(): void
    {
        /* Act */
        $options = $this->storage->optionsForType(ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertArrayHasKey('default', $options);
        $this->assertNotContains('Default Quote', $options);
    }

    #[Test]
    public function it_shadows_a_system_template_with_a_company_clone_of_the_same_slug(): void
    {
        /* Arrange — clone, then rename the clone's manifest name */
        $clone = $this->storage->clone('system', 'default', 'Default', ReportTemplateType::INVOICE);
        $this->storage->rename('company', $clone['slug'], 'Our House Default');

        /* Act */
        $options = $this->storage->optionsForType(ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertSame('Our House Default', $options['default']);
    }

    #[Test]
    public function it_persists_the_selected_template_slug_on_the_invoice(): void
    {
        /* Arrange */
        $this->storage->clone('system', 'default', 'Fancy', ReportTemplateType::INVOICE);
        $invoice = $this->invoice();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm(['template' => 'fancy'])
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'template' => 'fancy']);
    }

    #[Test]
    public function it_allows_clearing_the_template_back_to_the_company_default(): void
    {
        /* Arrange */
        $invoice = $this->invoice();
        $invoice->update(['template' => 'default']);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm(['template' => null])
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'template' => null]);
    }

    protected function invoice(): Invoice
    {
        $relation  = Relation::factory()->for($this->company)->create();
        $numbering = \Modules\Core\Models\Numbering::factory()->for($this->company)->create([
            'type' => \Modules\Core\Enums\NumberingType::INVOICE->value,
        ]);

        return Invoice::factory()->for($this->company)->create([
            'customer_id'  => $relation->id,
            'user_id'      => $this->user->id,
            'numbering_id' => $numbering->id,
            'template'     => null,
        ]);
    }
}
