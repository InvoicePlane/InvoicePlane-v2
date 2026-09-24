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
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
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

    /**
     * RB-11 / S3-9 (#764) — aging fires its own query per invoice; skip it
     * unless the resolved template actually has the aging brick.
     */
    #[Test]
    public function it_skips_the_aging_query_when_the_template_has_no_aging_brick(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'No Aging Client']);
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_status' => 'overdue',
            'invoice_due_at' => '2025-11-05',
            'invoice_total'  => 250.0000,
        ]);

        \Illuminate\Support\Facades\DB::enableQueryLog();

        /* Act */
        $without      = $this->mapper->forInvoice($invoice->fresh(), ['header_company', 'detail_items']);
        $agingQueries = collect(\Illuminate\Support\Facades\DB::getQueryLog())
            ->filter(fn (array $q): bool => str_contains($q['query'], 'invoice_status') && str_contains($q['query'], 'in ('))
            ->count();
        \Illuminate\Support\Facades\DB::flushQueryLog();

        $with = $this->mapper->forInvoice($invoice->fresh(), ['detail_customer_aging']);

        /* Assert */
        $this->assertSame([], $without['aging_items']);
        $this->assertSame('0.00', $without['aging_totals']['total_due']);
        $this->assertSame(0, $agingQueries, 'no open-invoice aging query should run without the aging brick');
        $this->assertNotEmpty($with['aging_items'], 'aging is still computed when the brick is present');
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
    public function it_populates_project_and_tasks_and_project_items_for_invoice_with_billed_tasks(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Project Client']);
        $project  = Project::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'project_name'   => 'Website Redesign',
            'project_number' => 'PRJ-100',
            'start_at'       => '2026-01-01',
            'end_at'         => '2026-03-31',
            'project_status' => ProjectStatus::ACTIVE,
        ]);
        $task = Task::factory()->for($this->company)->create([
            'customer_id' => $relation->id,
            'project_id'  => $project->id,
            'task_number' => 'TSK-1',
            'task_name'   => 'Design Homepage',
            'description' => 'Wireframes & UI design',
            'task_price'  => 500.00,
            'due_at'      => '2026-02-15',
            'task_status' => TaskStatus::COMPLETED,
        ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-PRJ-01',
            'invoice_status' => 'draft',
        ]);
        $invoice->invoiceItems()->delete();

        InvoiceItem::create([
            'company_id'  => $this->company->id,
            'invoice_id'  => $invoice->id,
            'task_id'     => $task->id,
            'item_name'   => 'Design Homepage',
            'description' => 'Wireframes & UI design',
            'quantity'    => 5,
            'price'       => 100.00,
            'subtotal'    => 500.00,
            'tax_1'       => 0,
            'tax_total'   => 0,
            'total'       => 500.00,
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertArrayHasKey('project', $data);
        $this->assertSame('PRJ-100', $data['project']['project_number']);
        $this->assertSame('Website Redesign', $data['project']['project_name']);
        $this->assertSame('2026-01-01', $data['project']['start_at']);
        $this->assertSame('2026-03-31', $data['project']['end_at']);

        $this->assertArrayHasKey('tasks', $data);
        $this->assertCount(1, $data['tasks']);
        $this->assertSame('TSK-1', $data['tasks'][0]['task_number']);
        $this->assertSame('Design Homepage', $data['tasks'][0]['task_name']);
        $this->assertSame('Wireframes & UI design', $data['tasks'][0]['description']);
        $this->assertSame('500.00', $data['tasks'][0]['task_price']);

        $this->assertArrayHasKey('project_items', $data);
        $this->assertCount(1, $data['project_items']);
        $this->assertSame('Website Redesign', $data['project_items'][0]['project_name']);
        $this->assertSame('Design Homepage', $data['project_items'][0]['task_name']);
        $this->assertSame(5.0, $data['project_items'][0]['hours']);
        $this->assertSame('100.00', $data['project_items'][0]['rate']);
        $this->assertSame('500.00', $data['project_items'][0]['total']);
    }

    /**
     * RB-11 / S3-7 (#764) — no project/task brick in the template ⇒ neither
     * the project/task eager-load nor the project data build runs.
     */
    #[Test]
    public function it_skips_the_project_relation_load_when_no_project_brick_is_present(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Proj Skip Client']);
        $project  = Project::factory()->for($this->company)->create(['customer_id' => $relation->id, 'project_name' => 'Should Not Load']);
        Task::factory()->for($this->company)->create(['customer_id' => $relation->id, 'project_id' => $project->id]);
        $invoice = Invoice::factory()->for($this->company)->create(['customer_id' => $relation->id]);

        \Illuminate\Support\Facades\DB::enableQueryLog();

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh(), ['header_company', 'detail_items', 'footer_totals']);

        $projectQueries = collect(\Illuminate\Support\Facades\DB::getQueryLog())
            ->filter(fn (array $q): bool => str_contains($q['query'], '"projects"') || str_contains($q['query'], '"tasks"')
                || str_contains($q['query'], '`projects`') || str_contains($q['query'], '`tasks`'))
            ->count();

        /* Assert */
        $this->assertSame(0, $projectQueries, 'no project/task query should run without a project brick');
        $this->assertSame('', $data['project']['project_number']);
        $this->assertSame([], $data['tasks']);
        $this->assertSame([], $data['project_items']);
    }

    /**
     * RB-11 / S3-7 (#764) — a project brick in the template pulls the data
     * back in.
     */
    #[Test]
    public function it_loads_project_data_when_a_project_brick_is_present(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Proj Load Client']);
        Project::factory()->for($this->company)->create(['customer_id' => $relation->id, 'project_name' => 'Loaded', 'project_number' => 'PRJ-9']);
        $invoice = Invoice::factory()->for($this->company)->create(['customer_id' => $relation->id]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh(), ['header_project']);

        /* Assert */
        $this->assertSame('PRJ-9', $data['project']['project_number']);
    }

    /**
     * RB-11 / S3-7 (#764) — no expense brick ⇒ no expense eager-load, empty
     * expense_items.
     */
    #[Test]
    public function it_skips_the_expense_relation_load_when_no_expense_brick_is_present(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Exp Skip Client']);
        $invoice  = Invoice::factory()->for($this->company)->create(['customer_id' => $relation->id]);
        Expense::factory()->for($this->company)->create(['customer_id' => $relation->id, 'invoice_id' => $invoice->id]);

        \Illuminate\Support\Facades\DB::enableQueryLog();

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh(), ['header_company', 'detail_items']);

        $expenseQueries = collect(\Illuminate\Support\Facades\DB::getQueryLog())
            ->filter(fn (array $q): bool => str_contains($q['query'], '"expenses"') || str_contains($q['query'], '`expenses`'))
            ->count();

        /* Assert */
        $this->assertSame(0, $expenseQueries, 'no expense query should run without an expense brick');
        $this->assertSame([], $data['expense_items']);
    }

    #[Test]
    public function it_populates_project_and_tasks_from_customer_when_not_billed_on_invoice(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Project Client']);
        $project  = Project::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'project_name'   => 'Brand Audit',
            'project_number' => 'PRJ-200',
            'start_at'       => '2026-02-01',
            'end_at'         => '2026-04-01',
            'project_status' => ProjectStatus::PLANNED,
        ]);
        Task::factory()->for($this->company)->create([
            'customer_id' => $relation->id,
            'project_id'  => $project->id,
            'task_number' => 'TSK-2',
            'task_name'   => 'Audit Assets',
            'description' => 'Review brand assets',
            'task_price'  => 250.00,
            'due_at'      => '2026-02-28',
            'task_status' => TaskStatus::NOT_STARTED,
        ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-GEN-01',
            'invoice_status' => 'draft',
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertArrayHasKey('project', $data);
        $this->assertSame('PRJ-200', $data['project']['project_number']);
        $this->assertSame('Brand Audit', $data['project']['project_name']);

        $this->assertArrayHasKey('tasks', $data);
        $this->assertCount(1, $data['tasks']);
        $this->assertSame('TSK-2', $data['tasks'][0]['task_number']);
        $this->assertSame('Audit Assets', $data['tasks'][0]['task_name']);

        $this->assertArrayHasKey('project_items', $data);
        $this->assertCount(1, $data['project_items']);
        $this->assertSame('Brand Audit', $data['project_items'][0]['project_name']);
        $this->assertSame('Audit Assets', $data['project_items'][0]['task_name']);
        $this->assertSame('250.00', $data['project_items'][0]['rate']);
    }

    #[Test]
    public function it_returns_empty_project_and_tasks_when_customer_has_no_projects(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Empty Client']);
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-NO-PRJ',
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertArrayHasKey('project', $data);
        $this->assertSame('', $data['project']['project_number']);
        $this->assertSame('', $data['project']['project_name']);
        $this->assertSame([], $data['tasks']);
        $this->assertSame([], $data['project_items']);
    }

    #[Test]
    public function it_populates_project_and_tasks_and_project_items_for_quote(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Quote Client']);
        $project  = Project::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'project_name'   => 'Quote Project',
            'project_number' => 'PRJ-Q-1',
            'start_at'       => '2026-03-01',
            'end_at'         => '2026-05-01',
            'project_status' => ProjectStatus::PLANNED,
        ]);
        $task = Task::factory()->for($this->company)->create([
            'customer_id' => $relation->id,
            'project_id'  => $project->id,
            'task_number' => 'TSK-Q-1',
            'task_name'   => 'Scope Work',
            'task_price'  => 300.00,
            'task_status' => TaskStatus::NOT_STARTED,
        ]);

        $quote = Quote::factory()->for($this->company)->create([
            'prospect_id'  => $relation->id,
            'quote_number' => 'Q-PRJ-01',
        ]);
        $quote->quoteItems()->delete();

        QuoteItem::create([
            'company_id' => $this->company->id,
            'quote_id'   => $quote->id,
            'task_id'    => $task->id,
            'item_name'  => 'Scope Work',
            'quantity'   => 2,
            'price'      => 150.00,
            'subtotal'   => 300.00,
            'tax_1'      => 0,
            'tax_total'  => 0,
            'total'      => 300.00,
        ]);

        /* Act */
        $data = $this->mapper->forQuote($quote->fresh());

        /* Assert */
        $this->assertArrayHasKey('project', $data);
        $this->assertSame('PRJ-Q-1', $data['project']['project_number']);
        $this->assertSame('Quote Project', $data['project']['project_name']);

        $this->assertArrayHasKey('tasks', $data);
        $this->assertCount(1, $data['tasks']);
        $this->assertSame('TSK-Q-1', $data['tasks'][0]['task_number']);

        $this->assertArrayHasKey('project_items', $data);
        $this->assertCount(1, $data['project_items']);
        $this->assertSame('Quote Project', $data['project_items'][0]['project_name']);
        $this->assertSame('Scope Work', $data['project_items'][0]['task_name']);
        $this->assertSame(2.0, $data['project_items'][0]['hours']);
        $this->assertSame('150.00', $data['project_items'][0]['rate']);
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
