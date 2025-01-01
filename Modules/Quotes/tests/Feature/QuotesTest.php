<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Models\PaymentMethod;
use Modules\Products\Models\Product;
use Modules\Projects\Models\Task;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Resources\QuoteResource\Pages\CreateQuote;
use Modules\Quotes\Filament\Resources\QuoteResource\Pages\EditQuote;
use Modules\Quotes\Filament\Resources\QuoteResource\Pages\ManageQuotes;
use Modules\Quotes\Models\Quote;

class QuotesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;
    // endregion

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_quotes_index(): void
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

        Quote::factory()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertStatus(200)
            ->assertSee('::client_name::')
            ->assertSee('::quote_number::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_only_filtered_draft_quotes_index(): void
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
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::sent_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertStatus(200)
            ->assertSee('::draft_quote_number::')
            ->assertDontSee('::sent_quote_number::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_only_filtered_sent_quotes_index(): void
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
            ->assertStatus(200)
            ->assertSee('::sent_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_only_filtered_viewed_quotes_index(): void
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
            ->assertStatus(200)
            ->assertSee('::viewed_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_only_filtered_approved_quotes_index(): void
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
            ->assertStatus(200)
            ->assertSee('::approved_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_only_filtered_rejected_quotes_index(): void
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
            ->assertStatus(200)
            ->assertSee('::rejected_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_only_filtered_canceled_quotes_index(): void
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

        Quote::factory()->canceled()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::canceled_quote_number::',
        ]);

        Livewire::test(ManageQuotes::class)
            ->assertStatus(200)
            ->assertSee('::canceled_quote_number::')
            ->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
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
            ->assertStatus(200)
            ->assertSee('::draft_quote_number::')
            ->assertSee('::sent_quote_number::');
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "client_id": 1,
     *    "quote_number": "123-456-789",
     *    "quote_date_created": "2024-11-22",
     *    "quote_date_expires": "2024-12-22",
     *    "quote_status_id": "DRAFT",
     *    "quote_discount_amount": 10.5,
     *    "quote_discount_percent": 5
     * }
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_quote(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create();
        $payload = [
            'client_id'              => $client->client_id,
            'quote_number'           => '123-456-789',
            'quote_date_created'     => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'quote_date_expires'     => Carbon::now()->addMonth()->format('Y-m-d'),
            'quote_status_id'        => QuoteStatus::DRAFT,
            'quote_discount_amount'  => 10.5,
            'quote_discount_percent' => 5,
        ];

        Livewire::test(CreateQuote::class)
            ->assertStatus(201)
            ->set('data.quote_number', $payload['quote_number'])
            ->set('data.client_id', $payload['client_id'])
            ->set('data.quote_date', $payload['quote_date'])
            ->set('data.quote_due_date', $payload['quote_due_date'])
            ->set('data.quote_status', $payload['quote_status'])
            ->set('data.quote_total', $payload['quote_total'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotes', $payload);
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "client_id": null,
     *    "quote_number": null,
     *    "quote_status_id": null
     * }
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_create_a_quote_without_required_fields(): void
    {
        // $this->authenticate();

        $payload = [
            'client_id'       => null,
            'quote_number'    => null,
            'quote_status_id' => null,
        ];

        Livewire::test(CreateQuote::class)
            ->assertStatus(201)
            ->set('data.client_id', $payload['client_id'])
            ->set('data.quote_number', $payload['quote_number'])
            ->set('data.quote_status_id', $payload['quote_status_id'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotes', $payload);
    }

    /**
     * @test
     *
     * @payload
     * * {
     * *    "quote_status_id": "APPROVED"
     * * }
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_quote_status(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create(['client_name' => '::client_name::']);
        $quote = Quote::factory()->create(['quote_status_id' => QuoteStatus::DRAFT]);

        $payload = [
            'quote_number'   => 'Q12345',
            'client_id'      => $client->client_id,
            'quote_date'     => now()->toDateString(),
            'quote_due_date' => now()->addDays(7)->toDateString(),
            'quote_status'   => QuoteStatus::DRAFT,
            'quote_total'    => 50.00,
        ];

        Quote::factory()->create($payload);

        $updatedData = [
            'quote_status_id' => QuoteStatus::APPROVED,
        ];

        Livewire::test(EditQuote::class)
            ->assertStatus(200)
            ->set('data.quote_number', $payload['quote_number'])
            ->set('data.client_id', $payload['client_id'])
            ->set('data.quote_date', $payload['quote_date'])
            ->set('data.quote_due_date', $payload['quote_due_date'])
            ->set('data.quote_status', $payload['quote_status'])
            ->set('data.quote_total', $payload['quote_total'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotes', ['quote_id' => $quote->quote_id, 'quote_status_id' => $updatedData['quote_status_id']]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_quote(): void
    {
        // $this->authenticate();
        $quote = Quote::factory()->create();

        Livewire::test(ManageQuotes::class)
            ->callTableAction('delete', $quote->quote_id)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('quotes', ['quote_id' => $quote->quote_id]);
    }
    // endregion

    // region Custom Tests
    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_changes_client_of_a_quote(): void
    {
        // $this->authenticate();
        $quote = Quote::factory()->create();
        $client = Client::factory()->create();

        Livewire::test(ManageQuotes::class)
            ->callTableAction('changeClient', $quote->quote_id, ['client_id' => $client->client_id])
            ->assertStatus(200)
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
        // $this->authenticate();
        // Payload for adding a product
        $product = Product::factory()->create();
        $payload = [
            'product_id' => $product->id,
            'quantity'   => 2,
            'price'      => $product->product_price,
        ];

        $quote = Quote::factory()->create($payload);

        Livewire::test(ManageQuotes::class)
            ->assertStatus(201)
            ->set('data.product_id', $payload['product_id'])
            ->set('data.quantity', $payload['quantity'])
            ->set('data.price', $payload['price'])
            ->call('addProduct')
            ->assertHasNoErrors();
    }

    /**
     * @test
     * route('filament.ivpl.resources.filament.resources.quotes.add_task')
     *
     * @skip Not implemented yet
     */
    public function it_adds_a_task_to_a_quote(): void
    {
        // $this->authenticate();
        // Payload for adding a task
        $task = Task::factory()->create();
        $payload = [
            'task_id' => $task->id,
            'hours'   => 5,
            'rate'    => 100.00,
        ];

        $quote = Quote::factory()->create($payload);

        Livewire::test(ManageQuotes::class)
            ->assertStatus(201)
            ->set('data.product_id', $payload['product_id'])
            ->set('data.quantity', $payload['quantity'])
            ->set('data.price', $payload['price'])
            ->call('addProduct')
            ->assertHasNoErrors();
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
        // $this->authenticate();
        $quote = Quote::factory()->create();

        Livewire::test(ManageQuotes::class)
            ->callTableAction('generate_pdf', $quote->quote_id)
            ->assertStatus(200)
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
        $client = Client::factory()->create();
        $payload = [
            'client_id'              => $client->client_id,
            'quote_number'           => '123-456-789',
            'quote_date_created'     => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'quote_date_expires'     => Carbon::now()->addMonth()->format('Y-m-d'),
            'quote_status_id'        => QuoteStatus::DRAFT,
            'quote_discount_amount'  => 10.5,
            'quote_discount_percent' => 5,
        ];

        Livewire::test(CreateQuote::class)
            ->assertStatus(201)
            ->set('data.quote_number', $payload['quote_number'])
            ->set('data.client_id', $payload['client_id'])
            ->set('data.quote_date', $payload['quote_date'])
            ->set('data.quote_due_date', $payload['quote_due_date'])
            ->set('data.quote_status', $payload['quote_status'])
            ->set('data.quote_total', $payload['quote_total'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotes', $payload);
    }
    // endregion
}
