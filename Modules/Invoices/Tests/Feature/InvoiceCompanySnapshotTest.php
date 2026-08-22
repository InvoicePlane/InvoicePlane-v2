<?php

namespace Modules\Invoices\Tests\Feature;

use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceCopyService;
use Modules\Invoices\Services\InvoiceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('crud')]
class InvoiceCompanySnapshotTest extends AbstractCompanyPanelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->user);
    }

    #[Test]
    public function it_snapshots_the_companys_details_onto_the_invoice_at_creation(): void
    {
        /* Arrange */
        $this->company->update([
            'name'        => 'ACME Corp',
            'vat_number'  => 'BE0123456789',
            'id_number'   => 'ID-1',
            'coc_number'  => 'COC-1',
        ]);
        $customer = Relation::factory()->for($this->company)->customer()->create();

        /* Act */
        $invoice = app(InvoiceService::class)->createInvoice($this->invoicePayload($customer));

        /* Assert */
        $this->assertSame('ACME Corp', $invoice->company_name);
        $this->assertSame('BE0123456789', $invoice->company_vat_number);
        $this->assertSame('ID-1', $invoice->company_id_number);
        $this->assertSame('COC-1', $invoice->company_coc_number);
    }

    #[Test]
    public function it_does_not_change_an_existing_invoices_snapshot_when_the_company_is_renamed(): void
    {
        /* Arrange */
        $this->company->update(['name' => 'ACME Corp']);
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = app(InvoiceService::class)->createInvoice($this->invoicePayload($customer));

        /* Act */
        $this->company->update(['name' => 'New Company Name']);

        /* Assert */
        $this->assertSame('ACME Corp', $invoice->fresh()->company_name);
        $this->assertSame('New Company Name', $this->company->fresh()->name);
    }

    #[Test]
    public function it_renders_the_snapshotted_company_name_on_the_pdf_not_the_companys_current_name(): void
    {
        /* Arrange */
        $this->company->update(['name' => 'ACME Corp']);
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = app(InvoiceService::class)->createInvoice($this->invoicePayload($customer));
        $this->company->update(['name' => 'New Company Name']);

        /* Act */
        $html = app(InvoiceService::class)->renderHtml($invoice->fresh());

        /* Assert */
        $this->assertStringContainsString('ACME Corp', $html);
        $this->assertStringNotContainsString('New Company Name', $html);
    }

    #[Test]
    public function it_renders_the_snapshotted_id_and_coc_numbers_on_the_pdf_not_the_companys_current_ones(): void
    {
        /* Arrange */
        $this->company->update(['id_number' => 'ID-OLD', 'coc_number' => 'COC-OLD']);
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = app(InvoiceService::class)->createInvoice($this->invoicePayload($customer));
        $this->company->update(['id_number' => 'ID-NEW', 'coc_number' => 'COC-NEW']);

        /* Act */
        $html = app(InvoiceService::class)->renderHtml($invoice->fresh());

        /* Assert */
        $this->assertStringContainsString('ID-OLD', $html);
        $this->assertStringContainsString('COC-OLD', $html);
        $this->assertStringNotContainsString('ID-NEW', $html);
        $this->assertStringNotContainsString('COC-NEW', $html);
    }

    #[Test]
    public function it_falls_back_to_the_live_company_name_for_invoices_created_before_the_snapshot_existed(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $customer->getKey(),
            'invoice_status' => InvoiceStatus::SENT->value,
            'company_name'   => null,
        ]);

        /* Act */
        $html = app(InvoiceService::class)->renderHtml($invoice);

        /* Assert */
        $this->assertStringContainsString($this->company->name, $html);
    }

    #[Test]
    public function a_credit_note_inherits_the_parent_invoices_snapshot_not_the_live_company(): void
    {
        /* Arrange */
        $this->company->update(['name' => 'ACME Corp']);
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = app(InvoiceService::class)->createInvoice(array_merge(
            $this->invoicePayload($customer),
            ['invoice_status' => InvoiceStatus::PAID->value],
        ));
        $this->company->update(['name' => 'New Company Name']);

        /* Act */
        $creditNote = app(InvoiceService::class)->createCreditNote($invoice->fresh());

        /* Assert */
        $this->assertSame('ACME Corp', $creditNote->company_name);
    }

    #[Test]
    public function duplicating_an_invoice_snapshots_the_current_company_details(): void
    {
        /* Arrange */
        $this->company->update(['name' => 'ACME Corp']);
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = app(InvoiceService::class)->createInvoice($this->invoicePayload($customer));
        $this->company->update(['name' => 'New Company Name']);

        /* Act */
        $copy = app(InvoiceCopyService::class)->copy($invoice->fresh());

        /* Assert */
        $this->assertSame('New Company Name', $copy->company_name);
    }

    private function invoicePayload(Relation $customer): array
    {
        return [
            'customer_id'              => $customer->getKey(),
            'invoice_number'           => null,
            'invoice_status'           => InvoiceStatus::DRAFT->value,
            'invoiced_at'              => '2026-01-01',
            'invoice_due_at'           => '2026-01-31',
            'invoice_item_subtotal'    => 100,
        ];
    }
}
