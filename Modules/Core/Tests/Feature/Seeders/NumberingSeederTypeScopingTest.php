<?php

namespace Modules\Core\Tests\Feature\Seeders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Database\Seeders\ExpensesSeeder;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Database\Seeders\PaymentsSeeder;
use Modules\Quotes\Database\Seeders\QuotesSeeder;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

#[CoversClass(AbstractSeeder::class)]
class NumberingSeederTypeScopingTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_quote_type_numbering_when_none_exists_for_the_company(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $quote = $this->buildQuote($company->id);

        /* Assert */
        $numbering = Numbering::query()->find($quote->numbering_id);
        $this->assertSame(NumberingType::QUOTE, $numbering->type);
    }

    #[Test]
    public function it_creates_an_invoice_type_numbering_when_none_exists_for_the_company(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $invoice = $this->buildInvoice($company->id);

        /* Assert */
        $numbering = Numbering::query()->find($invoice->numbering_id);
        $this->assertSame(NumberingType::INVOICE, $numbering->type);
    }

    #[Test]
    public function it_does_not_reuse_a_wrong_type_numbering_scheme_for_a_seeded_quote(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        // This is the exact scenario that used to break: an Invoice-type scheme
        // already exists for the company, and the untyped findOrCreateNumbering()
        // used to pick it up via inRandomOrder()->first() regardless of type.
        $invoiceNumbering = Numbering::factory()->for($company)->ofType(NumberingType::INVOICE)->create();

        /* Act */
        $quote = $this->buildQuote($company->id);

        /* Assert */
        $numbering = Numbering::query()->find($quote->numbering_id);
        $this->assertSame(NumberingType::QUOTE, $numbering->type);
        $this->assertNotSame($invoiceNumbering->id, $numbering->id);
    }

    #[Test]
    public function it_does_not_reuse_a_wrong_type_numbering_scheme_for_a_seeded_invoice(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        $quoteNumbering = Numbering::factory()->for($company)->ofType(NumberingType::QUOTE)->create();

        /* Act */
        $invoice = $this->buildInvoice($company->id);

        /* Assert */
        $numbering = Numbering::query()->find($invoice->numbering_id);
        $this->assertSame(NumberingType::INVOICE, $numbering->type);
        $this->assertNotSame($quoteNumbering->id, $numbering->id);
    }

    #[Test]
    public function it_reuses_an_existing_quote_type_numbering_instead_of_creating_a_duplicate(): void
    {
        /* Arrange */
        $company  = Company::factory()->create();
        $existing = Numbering::factory()->for($company)->ofType(NumberingType::QUOTE)->create();

        /* Act */
        $quote = $this->buildQuote($company->id);

        /* Assert */
        $this->assertSame($existing->id, $quote->numbering_id);
        $this->assertSame(
            1,
            Numbering::query()->where('company_id', $company->id)->where('type', NumberingType::QUOTE->value)->count()
        );
    }

    #[Test]
    public function it_seeds_a_payment_type_numbering_scheme_even_though_payment_has_no_numbering_fk(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->buildOneViaSeeder(new PaymentsSeeder(), $company->id);

        /* Assert */
        $this->assertDatabaseHas('numbering', [
            'company_id' => $company->id,
            'type'       => NumberingType::PAYMENT->value,
        ]);
    }

    #[Test]
    public function it_seeds_an_expense_type_numbering_scheme_even_though_expense_has_no_numbering_fk(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $this->buildOneViaSeeder(new ExpensesSeeder(), $company->id);

        /* Assert */
        $this->assertDatabaseHas('numbering', [
            'company_id' => $company->id,
            'type'       => NumberingType::EXPENSE->value,
        ]);
    }

    #[Test]
    public function numbering_factory_of_type_forces_the_requested_type_and_matching_prefix(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act */
        $numbering = Numbering::factory()->for($company)->ofType(NumberingType::PROJECT)->create();

        /* Assert */
        $this->assertSame(NumberingType::PROJECT, $numbering->type);
        $this->assertSame(NumberingType::PROJECT->prefix(), $numbering->prefix);
    }

    private function buildQuote(int $companyId): Quote
    {
        $this->buildOneViaSeeder(new QuotesSeeder(), $companyId);

        return Quote::query()->where('company_id', $companyId)->latest('id')->firstOrFail();
    }

    private function buildInvoice(int $companyId): Invoice
    {
        $this->buildOneViaSeeder(new InvoicesSeeder(), $companyId);

        return Invoice::query()->where('company_id', $companyId)->latest('id')->firstOrFail();
    }

    /**
     * Call the protected buildOne() on a seeder after wiring its protected
     * companyId, bypassing run()/seedWithProgress() (which needs a console
     * Command instance unavailable in tests) while still exercising the exact
     * same numbering lookup/creation logic run() would use.
     */
    private function buildOneViaSeeder(AbstractSeeder $seeder, int $companyId): void
    {
        $ref = new ReflectionClass($seeder);

        $companyIdProperty = $ref->getProperty('companyId');
        $companyIdProperty->setAccessible(true);
        $companyIdProperty->setValue($seeder, $companyId);

        $buildOne = $ref->getMethod('buildOne');
        $buildOne->setAccessible(true);
        $buildOne->invoke($seeder);
    }
}
