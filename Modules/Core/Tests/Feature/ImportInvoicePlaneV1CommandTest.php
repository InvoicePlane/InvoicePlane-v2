<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Payments\Models\Payment;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;
use PHPUnit\Framework\Attributes\Test;

class ImportInvoicePlaneV1CommandTest extends AbstractTestCase
{
    use RefreshDatabase;

    private string $dumpFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dumpFile = module_path('Core', 'Tests/Fixtures/test_invoiceplane_v1_dump.sql');

        // Ensure test dump file exists
        if (! file_exists($this->dumpFile)) {
            $this->fail('Test dump file not found: ' . $this->dumpFile);
        }
    }

    #[Test]
    public function it_imports_data_without_company_id_and_creates_new_company(): void
    {
        /* Arrange */
        $initialCompanyCount = Company::count();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile' => $this->dumpFile,
        ])->assertSuccessful();

        /* Assert */
        $this->assertEquals($initialCompanyCount + 1, Company::count());

        $company = Company::latest('id')->first();
        $this->assertNotNull($company);
        $this->assertStringContainsString('Imported from InvoicePlane v1', $company->company_name);
    }

    #[Test]
    public function it_imports_data_into_existing_company(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        $initialCompanyCount = Company::count();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $this->assertEquals($initialCompanyCount, Company::count());
    }

    #[Test]
    public function it_imports_product_categories_correctly(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $categories = ProductCategory::where('company_id', $company->id)->get();
        $this->assertGreaterThanOrEqual(2, $categories->count());

        $servicesCategory = $categories->where('category_name', 'Services')->first();
        $this->assertNotNull($servicesCategory);
        $this->assertEquals($company->id, $servicesCategory->company_id);
    }

    #[Test]
    public function it_imports_product_units_correctly(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $units = ProductUnit::where('company_id', $company->id)->get();
        $this->assertGreaterThanOrEqual(2, $units->count());

        $hourUnit = $units->where('unit_name', 'Hour')->first();
        $this->assertNotNull($hourUnit);
        $this->assertEquals('Hours', $hourUnit->unit_name_plrl);
    }

    #[Test]
    public function it_imports_products_with_relationships(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $products = Product::where('company_id', $company->id)->get();
        $this->assertGreaterThanOrEqual(2, $products->count());

        $consulting = $products->where('product_name', 'Consulting')->first();
        $this->assertNotNull($consulting);
        $this->assertEquals('SRV001', $consulting->code);
        $this->assertEquals(100.00, $consulting->price);
        $this->assertNotNull($consulting->category_id);
        $this->assertNotNull($consulting->unit_id);
    }

    #[Test]
    public function it_imports_clients_as_relations(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $relations = Relation::where('company_id', $company->id)->get();
        $this->assertGreaterThanOrEqual(2, $relations->count());

        $client = $relations->where('company_name', 'Test Client 1')->first();
        $this->assertNotNull($client);
        $this->assertEquals('customer', $client->relation_type->value);
        $this->assertEquals('VAT123456', $client->vat_number);
        $this->assertEquals('active', $client->relation_status->value);
    }

    #[Test]
    public function it_imports_invoice_groups_as_numbering(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $numbering = Numbering::where('company_id', $company->id)
            ->where('type', 'invoice')
            ->get();

        $this->assertGreaterThanOrEqual(1, $numbering->count());

        $defaultGroup = $numbering->where('name', 'Default')->first();
        $this->assertNotNull($defaultGroup);
        $this->assertEquals('INV', $defaultGroup->prefix);
        $this->assertEquals(1001, $defaultGroup->next_id);
    }

    #[Test]
    public function it_imports_invoices_with_items(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $invoices = Invoice::where('company_id', $company->id)->get();
        $this->assertGreaterThanOrEqual(2, $invoices->count());

        $invoice = $invoices->where('invoice_number', 'INV-001')->first();
        $this->assertNotNull($invoice);
        $this->assertNotNull($invoice->customer_id);
        $this->assertEquals('sent', $invoice->invoice_status->value);
        $this->assertEquals(100.00, $invoice->invoice_item_subtotal);
        $this->assertEquals(21.00, $invoice->invoice_tax_total);
        $this->assertEquals(121.00, $invoice->invoice_total);

        // Check invoice items
        $items = InvoiceItem::where('company_id', $company->id)
            ->where('invoice_id', $invoice->id)
            ->get();

        $this->assertGreaterThanOrEqual(1, $items->count());

        $item = $items->first();
        $this->assertEquals('Consulting', $item->item_name);
        $this->assertEquals(1.00, $item->quantity);
        $this->assertEquals(100.00, $item->price);
    }

    #[Test]
    public function it_imports_quotes_with_items(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $quotes = Quote::where('company_id', $company->id)->get();
        $this->assertGreaterThanOrEqual(1, $quotes->count());

        $quote = $quotes->where('quote_number', 'QUO-001')->first();
        $this->assertNotNull($quote);
        $this->assertNotNull($quote->prospect_id);
        $this->assertEquals('sent', $quote->quote_status->value);
        $this->assertEquals(100.00, $quote->quote_item_subtotal);

        // Check quote items
        $items = QuoteItem::where('company_id', $company->id)
            ->where('quote_id', $quote->id)
            ->get();

        $this->assertGreaterThanOrEqual(1, $items->count());
    }

    #[Test]
    public function it_imports_payments_correctly(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $payments = Payment::where('company_id', $company->id)->get();
        $this->assertGreaterThanOrEqual(1, $payments->count());

        $payment = $payments->first();
        $this->assertNotNull($payment->invoice_id);
        $this->assertNotNull($payment->customer_id);
        $this->assertEquals('bank_transfer', $payment->payment_method);
        $this->assertEquals(54.50, $payment->payment_amount);
        $this->assertEquals('paid', $payment->payment_status);
    }

    #[Test]
    public function it_returns_failure_when_dump_file_not_found(): void
    {
        /* Arrange */
        $nonExistentFile = '/tmp/non_existent_dump.sql';

        /* Act & Assert */
        $this->artisan('import:db', [
            'dumpfile' => $nonExistentFile,
        ])->assertFailed();
    }

    #[Test]
    public function it_maintains_data_relationships(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])->assertSuccessful();

        /* Assert */
        $invoice = Invoice::where('company_id', $company->id)
            ->where('invoice_number', 'INV-001')
            ->first();

        $this->assertNotNull($invoice);
        $this->assertInstanceOf(Relation::class, $invoice->customer);
        $this->assertEquals('Test Client 1', $invoice->customer->company_name);

        // Check invoice items have products
        $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($invoiceItem);
        $this->assertInstanceOf(Product::class, $invoiceItem->product);
        $this->assertEquals('Consulting', $invoiceItem->product->product_name);
    }

    #[Test]
    public function it_shows_import_statistics(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->artisan('import:db', [
            'dumpfile'    => $this->dumpFile,
            '--company_id' => $company->id,
        ])
            ->expectsOutputToContain('Import completed successfully!')
            ->expectsOutputToContain('Product Categories')
            ->expectsOutputToContain('Products')
            ->expectsOutputToContain('Clients')
            ->expectsOutputToContain('Invoices')
            ->expectsOutputToContain('Payments')
            ->assertSuccessful();
    }
}
