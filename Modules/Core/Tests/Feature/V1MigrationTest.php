<?php

namespace Modules\Core\Tests\Feature;

use Modules\Clients\Models\Address;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\TaxRate;
use Modules\Core\Services\Migration\Support\V1SqlDumpParser;
use Modules\Core\Services\Migration\V1MigrationManager;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Payments\Models\Payment;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;
use PHPUnit\Framework\Attributes\Test;

class V1MigrationTest extends AbstractAdminPanelTestCase
{
    protected string $fixturePath;
    protected V1MigrationManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturePath = module_path('Core', 'Tests/Fixtures/v1_fixture.sql');
        $this->manager = app(V1MigrationManager::class);
    }

    #[Test]
    public function it_correctly_parses_v1_sql_dump_fixture(): void
    {
        $parser = new V1SqlDumpParser();
        $tables = $parser->parse($this->fixturePath);

        $this->assertArrayHasKey('ip_tax_rates', $tables);
        $this->assertCount(2, $tables['ip_tax_rates']);

        $this->assertArrayHasKey('ip_clients', $tables);
        $this->assertCount(3, $tables['ip_clients']);

        $this->assertArrayHasKey('ip_products', $tables);
        $this->assertCount(6, $tables['ip_products']);

        $this->assertArrayHasKey('ip_invoices', $tables);
        $this->assertCount(5, $tables['ip_invoices']);

        $this->assertArrayHasKey('ip_invoice_items', $tables);
        $this->assertCount(8, $tables['ip_invoice_items']);

        $this->assertArrayHasKey('ip_payments', $tables);
        $this->assertCount(4, $tables['ip_payments']);

        $this->assertArrayHasKey('ip_quotes', $tables);
        $this->assertCount(2, $tables['ip_quotes']);

        $this->assertArrayHasKey('ip_projects', $tables);
        $this->assertCount(1, $tables['ip_projects']);

        $this->assertArrayHasKey('ip_tasks', $tables);
        $this->assertCount(2, $tables['ip_tasks']);
    }

    #[Test]
    public function it_performs_dry_run_without_writing_database_records(): void
    {
        /** @var Company $targetCompany */
        $targetCompany = Company::factory()->create();

        $context = $this->manager->createContextFromSql(
            $this->fixturePath,
            $targetCompany,
            $this->superAdmin,
            dryRun: true
        );

        $inspection = $this->manager->inspect($context);

        $this->assertEquals(2, $inspection['entities']['tax_rates']['source_count']);
        $this->assertEquals(3, $inspection['entities']['clients']['source_count']);
        $this->assertEquals(6, $inspection['entities']['products']['source_count'] - 4); // 6 products + 2 families + 2 units
        $this->assertEquals(5, $inspection['entities']['invoices']['source_count']);
        $this->assertEquals(4, $inspection['entities']['payments']['source_count']);
        $this->assertEquals(2, $inspection['entities']['quotes']['source_count']);

        // Run dry run
        $result = $this->manager->run($context);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_dry_run']);

        // Assert 0 rows were written to target company
        $this->assertEquals(0, Relation::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals(0, Invoice::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals(0, Quote::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals(0, Product::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals(0, Payment::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
    }

    #[Test]
    public function it_migrates_all_v1_entities_accurately_into_target_company(): void
    {
        /** @var Company $targetCompany */
        $targetCompany = Company::factory()->create();

        $context = $this->manager->createContextFromSql(
            $this->fixturePath,
            $targetCompany,
            $this->superAdmin,
            dryRun: false
        );

        $result = $this->manager->run($context);

        $this->assertTrue($result['success'], 'Migration errors: ' . json_encode($result['errors']));
        $this->assertFalse($result['is_dry_run']);

        // 1. Tax Rates
        $taxRates = TaxRate::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(2, $taxRates);
        $this->assertNotNull($taxRates->firstWhere('name', 'Standard VAT'));
        $this->assertEquals(20.00, (float) $taxRates->firstWhere('name', 'Standard VAT')->rate);

        // 2. Clients, Contacts, Addresses, Communications
        $relations = Relation::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(3, $relations);

        $acme = $relations->firstWhere('company_name', 'Acme Corp');
        $this->assertNotNull($acme);
        $this->assertEquals('US123456789', $acme->vat_number);

        // Primary contact
        $acmeContact = Contact::withoutGlobalScopes()->where('relation_id', $acme->id)->first();
        $this->assertNotNull($acmeContact);
        $this->assertEquals('Acme Corp', $acmeContact->first_name);
        $this->assertEquals('Smith', $acmeContact->last_name);

        // Address
        $acmeAddress = Address::withoutGlobalScopes()->where('addressable_id', $acme->id)->first();
        $this->assertNotNull($acmeAddress);
        $this->assertEquals('123 Market St', $acmeAddress->address_1);
        $this->assertEquals('San Francisco', $acmeAddress->city);
        $this->assertEquals('94105', $acmeAddress->postal_code);

        // Communications (email/phone)
        $comms = Communication::withoutGlobalScopes()->where('communicationable_id', $acmeContact->id)->get();
        $this->assertTrue($comms->contains('communication_value', 'billing@acme.test'));
        $this->assertTrue($comms->contains('communication_value', '+1-555-0199'));

        // 3. Products, Categories, Units
        $categories = ProductCategory::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertTrue($categories->contains('category_name', 'Hardware'));
        $this->assertTrue($categories->contains('category_name', 'Services'));

        $units = ProductUnit::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertTrue($units->contains('unit_name', 'Piece'));
        $this->assertTrue($units->contains('unit_name', 'Hour'));

        $products = Product::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(6, $products);
        $this->assertNotNull($products->firstWhere('product_name', 'Wireless Mouse'));
        $this->assertEquals(25.00, (float) $products->firstWhere('product_name', 'Wireless Mouse')->price);

        // 4. Invoices and Items
        $invoices = Invoice::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(5, $invoices);

        $invoiceItems = InvoiceItem::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(8, $invoiceItems);

        // Check statuses
        $inv1 = $invoices->firstWhere('invoice_number', 'INV-1001');
        $this->assertEquals(InvoiceStatus::PAID, $inv1->invoice_status);

        $inv4 = $invoices->firstWhere('invoice_number', 'INV-1004');
        $this->assertEquals(InvoiceStatus::DRAFT, $inv4->invoice_status);

        // 5. Payments
        $payments = Payment::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(4, $payments);
        $this->assertEquals(162.00, (float) $inv1->payments()->sum('payment_amount'));

        // 6. Quotes and Items
        $quotes = Quote::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(2, $quotes);
        $this->assertEquals(2, QuoteItem::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());

        $quo2 = $quotes->firstWhere('quote_number', 'QUO-2002');
        $this->assertEquals(QuoteStatus::APPROVED, $quo2->quote_status);

        // 7. Projects & Tasks
        $projects = Project::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(1, $projects);
        $this->assertEquals('Infrastructure Overhaul', $projects->first()->project_name);

        $tasks = Task::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(2, $tasks);

        // 8. Custom Fields
        $customFields = CustomField::withoutGlobalScopes()->where('company_id', $targetCompany->id)->get();
        $this->assertCount(1, $customFields);
        $this->assertEquals('Account Manager', $customFields->first()->custom_field_label);
    }

    #[Test]
    public function it_verifies_financial_invariants_across_all_migrated_invoices_and_quotes(): void
    {
        /** @var Company $targetCompany */
        $targetCompany = Company::factory()->create();

        $context = $this->manager->createContextFromSql(
            $this->fixturePath,
            $targetCompany,
            $this->superAdmin,
            dryRun: false
        );

        $result = $this->manager->run($context);

        $invariants = $result['financial_invariants'];
        $this->assertTrue($invariants['passed'], 'Financial invariants validation failed: ' . json_encode($invariants['mismatches']));
        $this->assertEquals(5, $invariants['invoices_checked']);
        $this->assertEquals(2, $invariants['quotes_checked']);
        $this->assertEquals(7, $invariants['passed_count']);
        $this->assertEquals(0, $invariants['failed_count']);

        // Spot check invoice 1: Total 162, Paid 162, Balance 0
        $inv1 = Invoice::withoutGlobalScopes()->where('company_id', $targetCompany->id)->where('invoice_number', 'INV-1001')->first();
        $this->assertEquals(162.00, (float) $inv1->invoice_total);
        $this->assertEquals(162.00, (float) $inv1->payments()->sum('payment_amount'));
        $this->assertEquals(0.00, (float) $inv1->invoice_total - (float) $inv1->payments()->sum('payment_amount'));

        // Spot check invoice 5: Total 960, Paid 400, Balance 560
        $inv5 = Invoice::withoutGlobalScopes()->where('company_id', $targetCompany->id)->where('invoice_number', 'INV-1005')->first();
        $this->assertEquals(960.00, (float) $inv5->invoice_total);
        $this->assertEquals(400.00, (float) $inv5->payments()->sum('payment_amount'));
        $this->assertEquals(560.00, (float) $inv5->invoice_total - (float) $inv5->payments()->sum('payment_amount'));
    }

    #[Test]
    public function it_is_idempotent_and_does_not_create_duplicate_records(): void
    {
        /** @var Company $targetCompany */
        $targetCompany = Company::factory()->create();

        $context1 = $this->manager->createContextFromSql(
            $this->fixturePath,
            $targetCompany,
            $this->superAdmin,
            dryRun: false
        );
        $this->manager->run($context1);

        $relationCountAfterFirst = Relation::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count();
        $invoiceCountAfterFirst = Invoice::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count();
        $quoteCountAfterFirst = Quote::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count();

        // Run second time on same target company
        $context2 = $this->manager->createContextFromSql(
            $this->fixturePath,
            $targetCompany,
            $this->superAdmin,
            dryRun: false
        );
        $this->manager->run($context2);

        $this->assertEquals($relationCountAfterFirst, Relation::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals($invoiceCountAfterFirst, Invoice::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals($quoteCountAfterFirst, Quote::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
    }

    #[Test]
    public function it_can_rollback_a_migration_batch(): void
    {
        /** @var Company $targetCompany */
        $targetCompany = Company::factory()->create();

        $context = $this->manager->createContextFromSql(
            $this->fixturePath,
            $targetCompany,
            $this->superAdmin,
            dryRun: false
        );
        $this->manager->run($context);

        $this->assertEquals(3, Relation::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals(5, Invoice::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());

        // Rollback
        $rollbackRes = $this->manager->rollback($context);
        $this->assertTrue($rollbackRes['success']);

        $this->assertEquals(0, Invoice::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals(0, Quote::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
        $this->assertEquals(0, Relation::withoutGlobalScopes()->where('company_id', $targetCompany->id)->count());
    }
}
