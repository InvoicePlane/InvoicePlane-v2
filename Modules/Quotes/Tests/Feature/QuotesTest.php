<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Modules\Clients\Models\Client;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Models\PaymentMethod;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductFamily;
use Modules\Products\Models\ProductUnit;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\QuoteResource;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\CreateQuote;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\EditQuote;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\ListQuotes;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(QuoteResource::class)]
class QuotesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_quotes(): void
    {
        $this->markTestIncomplete();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Quote::factory()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        Livewire::test(ListQuotes::class)
            ->assertSee('::client_name::')
            ->assertSee('::quote_number::');
    }

    #[Test]
    #[Group('smoke')]
    public function it_shows_only_filtered_draft_quotes_index(): void
    {
        $this->markTestSkipped('Not implemented');
        // $this->authenticate();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Quote::factory()->draft()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::draft_quote_number::',
        ]);

        Quote::factory()->sent()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::sent_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertSuccessful()
            ->assertSee('::draft_quote_number::')
            ->assertDontSee('::sent_quote_number::');
    }


    public function it_shows_only_filtered_sent_quotes_index(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Quote::factory()->sent()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::sent_quote_number::',
        ]);

        Quote::factory()->draft()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::draft_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertSuccessful()
            ->assertSee('::sent_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }


    public function it_shows_only_filtered_viewed_quotes_index(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Quote::factory()->viewed()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::viewed_quote_number::',
        ]);

        Quote::factory()->draft()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::draft_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertSuccessful()
            ->assertSee('::viewed_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }


    public function it_shows_only_filtered_approved_quotes_index(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Quote::factory()->approved()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::approved_quote_number::',
        ]);

        Quote::factory()->draft()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::draft_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertSuccessful()
            ->assertSee('::approved_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }


    public function it_shows_only_filtered_rejected_quotes_index(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Quote::factory()->rejected()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::rejected_quote_number::',
        ]);

        Quote::factory()->draft()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::draft_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertSuccessful()
            ->assertSee('::rejected_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }


    public function it_shows_only_filtered_canceled_quotes_index(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Quote::factory()->draft()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::draft_quote_number::',
        ]);

        Quote::factory()->canceled()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::canceled_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertSuccessful()
            ->assertSee('::canceled_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }


    public function it_shows_all_quotes_index(): void
    {
        // $this->authenticate();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Quote::factory()->draft()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::draft_quote_number::',
        ]);

        Quote::factory()->sent()->create([
            'quote_number' => '::sent_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertSuccessful()
            ->assertSee('::draft_quote_number::')
            ->assertSee('::sent_quote_number::');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "prospect_id": 2,
     *   "document_group_id": 3,
     *   "user_id": 4,
     *   "quote_number": "QUO-001",
     *   "quote_status": "draft",
     *   "quoted_at": "2025-04-30",
     *   "quote_expires_at": "2025-05-30",
     *   "quote_discount_amount": "10.00",
     *   "quote_discount_percent": "5.00",
     *   "quote_item_tax_total": "2.50",
     *   "quote_item_subtotal": "50.00",
     *   "quote_tax_total": "2.50",
     *   "quote_total": "52.50",
     *   "quote_password": "secret123",
     *   "quote_url_key": "abc123"
     * }
     */
    public function it_creates_a_quote(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'             => $company->id,
            'prospect_id'            => 2,
            'document_group_id'      => 3,
            'user_id'                => 4,
            'quote_number'           => 'QUO-001',
            'quote_status'           => QuoteStatus::DRAFT,
            'quoted_at'              => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'quote_date_expires'     => Carbon::now()->addMonth()->format('Y-m-d'),
            'quote_discount_amount'  => 10.00,
            'quote_discount_percent' => 5.00,
            'quote_item_tax_total'   => 2.50,
            'quote_item_subtotal'    => 50.00,
            'quote_tax_total'        => 2.50,
            'quote_total'            => 52.50,
            'quote_password'         => 'secret123',
            'quote_url_key'          => 'abc123',
        ];

        Livewire::test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "prospect_id": 2,
     *   "document_group_id": 3,
     *   "user_id": 4,
     *   "quote_number": "QUO-001",
     *   "quoted_at": "2025-04-30",
     *   "quote_expires_at": "2025-05-30",
     *   "quote_discount_amount": "10.00",
     *   "quote_discount_percent": "5.00",
     *   "quote_item_tax_total": "2.50",
     *   "quote_item_subtotal": "50.00",
     *   "quote_tax_total": "2.50",
     *   "quote_total": "52.50",
     *   "quote_password": "secret123",
     *   "quote_url_key": "abc123"
     * }
     */
    public function it_fails_to_create_quote_without_required_quote_status(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $payload = [
            'company_id'   => $company->id,
            'quote_number' => 'QUO-001',
        ];

        Livewire::test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['quote_status' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "prospect_id": "Value",
     * "document_group_id": "Value",
     * "user_id": "Value",
     * "quote_number": "Example",
     * "quote_status": "Value",
     * "quoted_at": "2025-04-30",
     * "quote_expires_at": "2025-04-30",
     * "quote_discount_amount": "9.99",
     * "quote_discount_percent": "9.99",
     * "quote_item_tax_total": "9.99",
     * "quote_item_subtotal": "9.99",
     * "quote_tax_total": "9.99",
     * "quote_total": "9.99",
     * "quote_password": "Example",
     * "quote_url_key": "Example"
     * }
     */
    public function it_updates_a_quote(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $quote = Quote::factory()->create(['invoice_id' => null, 'quote_status_id' => QuoteStatus::DRAFT, ]);

        $record = Quote::factory()->create();

        $payload = [
            'company_id'             => 'Value',
            'prospect_id'            => 'Value',
            'document_group_id'      => 'Value',
            'user_id'                => 'Value',
            'quote_number'           => 'Example',
            'quote_status'           => 'Value',
            'quoted_at'              => '2025-04-30',
            'quote_expires_at'       => '2025-04-30',
            'quote_discount_amount'  => 9.99,
            'quote_discount_percent' => 9.99,
            'quote_item_tax_total'   => 9.99,
            'quote_item_subtotal'    => 9.99,
            'quote_tax_total'        => 9.99,
            'quote_total'            => 9.99,
            'quote_password'         => 'Example',
            'quote_url_key'          => 'Example',
        ];

        $updatedData = [
            'quote_status_id' => QuoteStatus::APPROVED,
        ];

        Livewire::test(EditQuote::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('quotes', ['quote_id' => $quote->quote_id, 'quote_status_id' => $updatedData['quote_status_id']]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "prospect_id": "Value",
     * "document_group_id": "Value",
     * "user_id": "Value",
     * "quote_number": "Example",
     * "quote_status": "Value",
     * "quoted_at": "2025-04-30",
     * "quote_expires_at": "2025-04-30",
     * "quote_discount_amount": "9.99",
     * "quote_discount_percent": "9.99",
     * "quote_item_tax_total": "9.99",
     * "quote_item_subtotal": "9.99",
     * "quote_tax_total": "9.99",
     * "quote_total": "9.99",
     * "quote_password": "Example",
     * "quote_url_key": "Example"
     * }
     */
    public function it_deletes_a_quote(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);
        $quote = Quote::factory()->create();

        Livewire::test(ListQuotes::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('quotes', ['id' => $record->id]);
    }
    // endregion

    // region usp
    /**
     * @payload ["quoteId" => $quote->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_converts_a_quote_into_an_invoice(): void
    {
        $this->markTestIncomplete();

        $quote = Quote::factory()->create([
            'total'  => 300,
            'status' => 'approved',
        ]);

        $component = Livewire::test(ConvertQuoteToInvoice::class, ['quoteId' => $quote->id])
            ->fillForm(['due_date' => now()->addWeek()->toDateString()])
            ->call('convert');

        $component
            ->assertHasNoFormErrors()
            ->assertEmitted('quoteConverted')
            ->assertRedirect(route('invoices.edit', ['invoice' => Invoice::latest()->first()->id]));

        $invoice = Invoice::latest()->first();

        if (app()->isLocal()) {
            dump($invoice);
        }

        $this->assertDatabaseHas('invoices', [
            'id'     => $invoice->id,
            'amount' => $quote->total,
        ]);
        $this->assertDatabaseHas('quotes', [
            'id'     => $quote->id,
            'status' => 'converted',
        ]);
    }

    public function it_changes_client_of_a_quote(): void
    {
        $this->markTestIncomplete('changeClient action not implemented');
        // $this->authenticate();
        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);
        $quote  = Quote::factory()->create();
        $client = Client::factory()->create();

        Livewire::test(ManageQuotes::class)
            ->callTableAction('changeClient', $quote->quote_id, ['client_id' => $client->client_id])
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotes', [
            'quote_id'  => $quote->quote_id,
            'client_id' => $client->client_id,
        ]);
    }
    // endregion

    // region Spicy Tests
    /**
     * @test
     * route('filament.ivpl.resources.filament.resources.quotes.add_product')
     *
     * @skip Not implemented yet
     */
    public function it_adds_a_product_to_a_quote(): void
    {
        $this->markTestIncomplete('addProduct action not implemented');
        // $this->authenticate();
        $user         = User::factory()->create();
        $client       = Client::factory()->create(['client_name' => '::client_name::']);
        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $tax_rate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '9',
        ]);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $product = Product::factory()->create([
            'family_id'    => $productFamily,
            'product_sku'  => '::product_sku::',
            'product_name' => '::product_name::',
            'tax_rate_id'  => $taxRate,
            'unit_id'      => $productUnit,
        ]);

        $payload = [
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ];

        $quote = Quote::factory()->create($payload);

        Livewire::test(ManageQuotes::class)
            ->callTableAction('addProduct', $quote->quote_id, ['client_id' => $client->client_id])
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotes', [
            'quote_id'  => $quote->quote_id,
            'client_id' => $client->client_id,
        ]);
    }

    /**
     * @test
     * route('filament.ivpl.resources.filament.resources.quotes.add_task')
     *
     * @skip Not implemented yet
     */
    public function it_adds_a_task_to_a_quote(): void
    {
        $this->markTestIncomplete('addTask action needs to be implemented');
        // $this->authenticate();

        $user         = User::factory()->create();
        $client       = Client::factory()->create(['client_name' => '::client_name::']);
        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $tax_rate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '9',
        ]);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $task = Task::factory()->create(['project_id' => $project->project_id]);

        $payload = [
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ];

        $quote = Quote::factory()->create($payload);

        Livewire::test(CreateQuote::class)
            ->assertSuccessful()
            ->set('data.invoice_id', $payload['invoice_id'])
            ->set('data.invoice_group_id', $payload['invoice_group_id'])
            ->set('data.quote_number', $payload['quote_number'])
            ->call('addTask');
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.quotes.generate_pdf', ['record' => $quote->id])
     *
     * @skip Not implemented yet
     */
    public function it_generates_a_quote_pdf(): void
    {
        $this->markTestIncomplete('generatePdf action Not implemented');
        // $this->authenticate();
        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);
        $quote = Quote::factory()->create();

        Livewire::test(ManageQuotes::class)
            ->callTableAction('generatePdf', $quote->quote_id)
            ->assertSuccessful()
            ->assertHasNoErrors();
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.quotes.calculate_totals', ['record' => $quote->id])
     *
     * @skip Not implemented yet
     */
    public function it_calculates_totals_for_a_quote(): void
    {
        // $this->authenticate();
        $client       = Client::factory()->create();
        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $invoice = Invoice::factory()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);
        $payload = [
            'client_id'              => $client->client_id,
            'invoice_id'             => $invoice->invoice_id,
            'quote_number'           => '123-456-789',
            'quote_date_created'     => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'quote_date_expires'     => Carbon::now()->addMonth()->format('Y-m-d'),
            'quote_status_id'        => QuoteStatus::DRAFT,
            'quote_discount_amount'  => 10.5,
            'quote_discount_percent' => 5,
        ];
        Quote::factory()->create($payload);

        Livewire::test(CreateQuote::class)
            ->assertSuccessful()
            ->set('data.quote_number', $payload['quote_number'])
            ->set('data.client_id', $payload['client_id'])
            ->set('data.quote_date_expires', $payload['quote_date_expires'])
            ->set('data.quote_status_id', $payload['quote_status_id'])
            ->call('create');

        $this->assertDatabaseHas('quotes', $payload);
    }

    // endregion
}
