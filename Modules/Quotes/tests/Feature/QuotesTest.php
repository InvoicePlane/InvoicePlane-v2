<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Models\PaymentMethod;
use Modules\Products\Models\Product;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;

class QuotesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;
    // endregion

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /**
     * @test
     */
    public function it_will_fail(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $user = User::factory()->create();

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
            'invoice_id'       => null,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index'));
        $response->assertStatus(200);
        $response->assertSee('::client_name::');
        $response->assertSee('::quote_number::');
    }

    /**
     * @test
     */
    public function it_shows_quotes_index(): void
    {
        $user = User::factory()->create();

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

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index'));
        $response->assertStatus(200);
        $response->assertSee('::client_name::');
        $response->assertSee('::quote_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_draft_quotes_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

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

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index', ['status' => 'draft']));
        $response->assertStatus(200);
        $response->assertSee('::draft_quote_number::');
        $response->assertDontSee('::sent_quote_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_sent_quotes_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

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

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index', ['status' => 'sent']));
        $response->assertStatus(200);
        $response->assertSee('::sent_quote_number::');
        $response->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_viewed_quotes_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

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

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index', ['status' => 'viewed']));
        $response->assertStatus(200);
        $response->assertSee('::viewed_quote_number::');
        $response->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_approved_quotes_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

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

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index', ['status' => 'approved']));
        $response->assertStatus(200);
        $response->assertSee('::approved_quote_number::');
        $response->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_rejected_quotes_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

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

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index', ['status' => 'rejected']));
        $response->assertStatus(200);
        $response->assertSee('::rejected_quote_number::');
        $response->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_canceled_quotes_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

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

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index', ['status' => 'canceled']));
        $response->assertStatus(200);
        $response->assertSee('::canceled_quote_number::');
        $response->assertDontSee('::draft_quote_number::');
    }

    /**
     * @test
     */
    public function it_shows_all_quotes_index(): void
    {
        $user = User::factory()->create();

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

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('quotes.index', ['status' => 'all']));
        $response->assertStatus(200);
        $response->assertSee('::draft_quote_number::');
        $response->assertSee('::sent_quote_number::');
    }

    /** @test */
    public function it_creates_a_quote(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
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
         */
        $client = Client::factory()->create();
        $payload = [
            'client_id'              => $client->client_id,
            'quote_number'           => '123-456-789',
            'quote_date_created'     => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'quote_date_expires'     => Carbon::now()->addMonth()->format('Y-m-d'),
            'quote_status_id'        => Quote::DRAFT,
            'quote_discount_amount'  => 10.5,
            'quote_discount_percent' => 5,
        ];

        // Act
        $response = $this->post(route('filament.ivpl.resources.filament.resources.quotes.store'), $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('quotes', ['client_id' => $client->client_id]);
    }

    /** @test */
    public function it_fails_to_create_a_quote_without_required_fields(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "client_id": null,
         *    "quote_number": null,
         *    "quote_status_id": null
         * }
         */
        $payload = [
            'client_id'       => null,
            'quote_number'    => null,
            'quote_status_id' => null,
        ];

        // Act
        $response = $this->post(route('filament.ivpl.resources.filament.resources.quotes.store'), $payload);

        // Assert
        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_updates_a_quote_status(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "quote_status_id": "APPROVED"
         * }
         */
        $quote = Quote::factory()->create(['quote_status_id' => Quote::DRAFT]);

        $payload = [
            'quote_status_id' => Quote::APPROVED,
        ];

        // Act
        $response = $this->patch(route('filament.ivpl.resources.filament.resources.quotes.update', $quote->quote_id), $payload);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('quotes', ['quote_id' => $quote->quote_id, 'quote_status_id' => Quote::APPROVED]);
    }
    // endregion

    // region Custom Tests
    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_changes_the_client_of_a_quote(): void
    {
        // Payload for changing a client
        $quote = Quote::factory()->create();
        $newClient = Client::factory()->create();
        $payload = ['client_id' => $newClient->id];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('filament.ivpl.resources.filament.resources.quotes.change_client', ['record' => $quote->id]), $payload);
        $response->assertStatus(200);
    }
    // endregion

    // region Spicy Tests
    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_adds_a_product_to_a_quote(): void
    {
        // Payload for adding a product
        $quote = Quote::factory()->create();
        $product = Product::factory()->create();
        $payload = [
            'product_id' => $product->id,
            'quantity'   => 2,
            'price'      => $product->product_price,
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('filament.ivpl.resources.filament.resources.quotes.add_product', ['record' => $quote->id]), $payload);
        $response->assertStatus(201);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_adds_a_task_to_a_quote(): void
    {
        // Payload for adding a task
        $quote = Quote::factory()->create();
        $task = Task::factory()->create();
        $payload = [
            'task_id' => $task->id,
            'hours'   => 5,
            'rate'    => 100, // Assuming a fixed hourly rate for the task
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('filament.ivpl.resources.filament.resources.quotes.add_task', ['record' => $quote->id]), $payload);
        $response->assertStatus(201);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_generates_a_quote_pdf(): void
    {
        $quote = Quote::factory()->create();

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('filament.ivpl.resources.filament.resources.quotes.generate_pdf', ['record' => $quote->id]));
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_calculates_totals_for_a_quote(): void
    {
        $quote = Quote::factory()->create();

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->getJson(route('filament.ivpl.resources.filament.resources.quotes.calculate_totals', ['record' => $quote->id]));
        $response->assertStatus(200);
    }
}
