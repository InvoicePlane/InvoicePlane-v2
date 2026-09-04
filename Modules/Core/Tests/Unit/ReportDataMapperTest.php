<?php

namespace Modules\Core\Tests\Unit;

use Modules\Clients\Models\Relation;
use Modules\Core\Models\TaxRate;
use Modules\Core\Services\ReportDataMapper;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Models\Product;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;
use PHPUnit\Framework\Attributes\Test;

class ReportDataMapperTest extends AbstractCompanyPanelTestCase
{
    protected ReportDataMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new ReportDataMapper();
    }

    #[Test]
    public function it_populates_invoice_items_for_the_invoice_product_brick(): void
    {
        /* Arrange */
        $invoice = $this->invoiceWithProductItem();

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertArrayHasKey('invoice_items', $data);
        $this->assertCount(1, $data['invoice_items']);
        $this->assertSame('WIDGET-1', $data['invoice_items'][0]['sku']);
        $this->assertSame('Golden Widget', $data['invoice_items'][0]['description']);
        $this->assertSame('50.00', $data['invoice_items'][0]['unit_price']);
        $this->assertSame('121.00', $data['invoice_items'][0]['total']);
    }

    #[Test]
    public function it_populates_quote_items_for_the_quote_product_brick(): void
    {
        /* Arrange */
        $quote = $this->quoteWithProductItem();

        /* Act */
        $data = $this->mapper->forQuote($quote->fresh());

        /* Assert */
        $this->assertArrayHasKey('quote_items', $data);
        $this->assertCount(1, $data['quote_items']);
        $this->assertSame('WIDGET-1', $data['quote_items'][0]['sku']);
        $this->assertSame('Golden Quote Widget', $data['quote_items'][0]['description']);
    }

    #[Test]
    public function it_populates_expense_items_from_expenses_linked_to_the_invoice(): void
    {
        /* Arrange */
        $invoice  = $this->invoiceWithProductItem();
        $category = ExpenseCategory::factory()->for($this->company)->create(['category_name' => 'Travel']);
        $vendor   = Relation::factory()->for($this->company)->create(['company_name' => 'Acme Vendor']);

        Expense::factory()->for($this->company)->create([
            'invoice_id'     => $invoice->id,
            'category_id'    => $category->id,
            'vendor_id'      => $vendor->id,
            'customer_id'    => $invoice->customer_id,
            'expense_number' => 'EXP-0001',
            'expense_amount' => 42.5,
            'description'    => 'Client lunch',
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertArrayHasKey('expense_items', $data);
        $this->assertCount(1, $data['expense_items']);
        $this->assertSame('EXP-0001', $data['expense_items'][0]['expense_number']);
        $this->assertSame('Travel', $data['expense_items'][0]['category']);
        $this->assertSame('Acme Vendor', $data['expense_items'][0]['vendor']);
        $this->assertSame('42.50', $data['expense_items'][0]['amount']);
    }

    #[Test]
    public function it_places_an_open_invoice_in_the_correct_aging_bucket(): void
    {
        /* Arrange — "now" is frozen at 2026-01-01 by AbstractCompanyPanelTestCase */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Aging Client']);

        $current = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-CURRENT',
            'invoice_status' => 'sent',
            'invoiced_at'    => '2025-12-20',
            'invoice_due_at' => '2026-01-15',
            'invoice_total'  => 100.0000,
        ]);

        $overdue60 = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-OVERDUE-60',
            'invoice_status' => 'overdue',
            'invoiced_at'    => '2025-10-01',
            'invoice_due_at' => '2025-11-05',
            'invoice_total'  => 250.0000,
        ]);

        $anchorInvoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-ANCHOR',
            'invoice_status' => 'sent',
            'invoiced_at'    => '2026-01-01',
            'invoice_due_at' => '2026-02-01',
            'invoice_total'  => 10.0000,
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($anchorInvoice->fresh());

        /* Assert */
        $byNumber = collect($data['aging_items'])->keyBy('invoice_number');

        $this->assertSame('100.00', $byNumber['INV-CURRENT']['current']);
        $this->assertSame('-', $byNumber['INV-CURRENT']['days_60']);

        $this->assertSame('250.00', $byNumber['INV-OVERDUE-60']['days_60']);
        $this->assertSame('-', $byNumber['INV-OVERDUE-60']['current']);

        /* aging_totals also includes the anchor invoice itself (10.00, not yet due → current) */
        $this->assertSame('360.00', $data['aging_totals']['total_due']);
        $this->assertSame('110.00', $data['aging_totals']['current']);
        $this->assertSame('250.00', $data['aging_totals']['days_60']);
    }

    #[Test]
    public function it_excludes_paid_and_draft_invoices_from_aging(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Aging Client']);

        Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-DRAFT',
            'invoice_status' => 'draft',
            'invoice_due_at' => '2025-11-01',
            'invoice_total'  => 500.0000,
        ]);

        Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-PAID',
            'invoice_status' => 'paid',
            'invoice_due_at' => '2025-11-01',
            'invoice_total'  => 500.0000,
        ]);

        $anchorInvoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-ANCHOR',
            'invoice_status' => 'sent',
            'invoice_due_at' => '2026-02-01',
            'invoice_total'  => 10.0000,
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($anchorInvoice->fresh());

        /* Assert */
        $numbers = collect($data['aging_items'])->pluck('invoice_number')->all();
        $this->assertNotContains('INV-DRAFT', $numbers);
        $this->assertNotContains('INV-PAID', $numbers);
    }

    #[Test]
    public function it_eager_loads_company_addresses_and_communications_for_invoices(): void
    {
        /* Arrange */
        $invoice = $this->invoiceWithProductItem()->fresh();

        /* Act */
        $this->mapper->forInvoice($invoice);

        /* Assert — company.addresses/communications must already be loaded
         * by the time companyData() reads them, or every PDF render lazy-loads
         * both relations individually. */
        $this->assertTrue($invoice->company->relationLoaded('addresses'));
        $this->assertTrue($invoice->company->relationLoaded('communications'));
    }

    #[Test]
    public function it_eager_loads_company_addresses_and_communications_for_quotes(): void
    {
        /* Arrange */
        $quote = $this->quoteWithProductItem()->fresh();

        /* Act */
        $this->mapper->forQuote($quote);

        /* Assert */
        $this->assertTrue($quote->company->relationLoaded('addresses'));
        $this->assertTrue($quote->company->relationLoaded('communications'));
    }

    protected function invoiceWithProductItem(): Invoice
    {
        $taxRate = TaxRate::factory()->for($this->company)->create(['rate' => 21.00, 'is_active' => true]);
        $product = Product::factory()->for($this->company)->create(['code' => 'WIDGET-1']);

        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Golden Client Ltd']);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-GOLD-0001',
            'invoice_status' => 'sent',
            'invoiced_at'    => '2026-01-01',
            'invoice_due_at' => '2026-01-31',
            'invoice_total'  => 121.0000,
        ]);
        $invoice->invoiceItems()->delete();

        InvoiceItem::create([
            'company_id'  => $this->company->id,
            'invoice_id'  => $invoice->id,
            'product_id'  => $product->id,
            'tax_rate_id' => $taxRate->id,
            'item_name'   => 'Golden Widget',
            'quantity'    => 2,
            'price'       => 50.0000,
            'subtotal'    => 100.0000,
            'tax_1'       => 21.0000,
            'tax_total'   => 21.0000,
            'total'       => 121.0000,
        ]);

        /** @var Invoice $fresh */
        $fresh = $invoice->fresh();

        return $fresh;
    }

    protected function quoteWithProductItem(): Quote
    {
        $taxRate = TaxRate::factory()->for($this->company)->create(['rate' => 21.00, 'is_active' => true]);
        $product = Product::factory()->for($this->company)->create(['code' => 'WIDGET-1']);

        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Golden Client Ltd']);

        $quote = Quote::factory()->for($this->company)->create([
            'prospect_id'      => $relation->id,
            'quote_number'     => 'Q-GOLD-0001',
            'quote_status'     => 'sent',
            'quoted_at'        => '2026-01-01',
            'quote_expires_at' => '2026-01-31',
            'quote_total'      => 121.0000,
        ]);
        $quote->quoteItems()->delete();

        QuoteItem::create([
            'company_id'  => $this->company->id,
            'quote_id'    => $quote->id,
            'product_id'  => $product->id,
            'tax_rate_id' => $taxRate->id,
            'item_name'   => 'Golden Quote Widget',
            'quantity'    => 2,
            'price'       => 50.0000,
            'subtotal'    => 100.0000,
            'tax_1'       => 21.0000,
            'tax_total'   => 21.0000,
            'total'       => 121.0000,
        ]);

        /** @var Quote $fresh */
        $fresh = $quote->fresh();

        return $fresh;
    }
}
