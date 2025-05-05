<?php

namespace Modules\Quotes\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\ApiTestTrait;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\PaymentMethod;
use Modules\Products\Models\Product;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class QuotesApiTest extends AbstractTestCase
{
    use ApiTestTrait;
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
     *
     * @payload
     * [
     * {
     * "quote_id": 1,
     * "client_id": 1,
     * "quote_number": "123-456-789",
     * "quote_total": 230.00,
     * "quote_date_created": "2023-11-01"
     * },
     * {
     * "quote_id": 2,
     * "client_id": 2,
     * "quote_number": "987-654-321",
     * "quote_total": 150.00,
     * "quote_date_created": "2023-11-10"
     * }
     * ]
     */
    public function it_returns_quotes_index(): void
    {
        $this->markTestSkipped('Not implemented yet');

        $user   = User::factory()->create();
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

        Quote::factory(5)->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        $response = $this->get(route('api.quotes.index'));

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'status',
                    'quote_number',
                    'created',
                    'due_date',
                    'amount',
                    'client',
                ],
            ],
        ]);

        $response->assertJsonFragment(['quote_number' => '::quote_number::']);
    }


    public function it_fails_to_retrieve_quotes_without_authentication(): void
    {
        $this->markTestSkipped('Not implemented yet');

        // Act
        $response = $this->getJson(route('api.quotes.index'));

        // Assert
        $response->assertStatus(401); // Unauthorized
    }


    public function it_creates_a_quote(): void
    {
        $this->markTestSkipped('Not implemented yet.');

        $user   = User::factory()->create();
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

        $initialQuote = Quote::factory()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.quotes.store'), [
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_status_id'  => PaymentStatus::DRAFT,
            'quote_number'     => '::quote_number::',
        ]);

        $response->assertSuccessful();

        $initialQuote->refresh();

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'number',
                    'date',
                    'status',
                    'total',
                    'client',
                ],
            ],
        ]);
        $response->assertJsonFragment(['quote_number' => '::quote_number::']);
    }


    public function it_returns_an_error_when_posting_quote_without_status_id(): void
    {
        $user   = User::factory()->create();
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

        $initialQuote = Quote::factory()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.quotes.store'), [
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('quote_status_id', 'errors');
    }


    public function it_updates_a_quote(): void
    {
        $this->markTestSkipped('Not implemented yet');

        $user   = User::factory()->create();
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

        $initialQuote = Quote::factory()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        $updatedData = [
            'quote_number' => '::updated_quote_number::',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.quotes.update', ['quote' => $initialQuote->quote_id]), $updatedData);

        $response->assertSuccessful();

        $initialQuote->refresh();

        $response->assertJsonFragment(['quote_number' => '::updated_quote_number::']);

        $this->assertEquals($updatedData['quote_number'], $initialQuote->quote_number);
    }


    public function it_can_update_quote_line_items(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->withAuthentication();

        // Arrange
        $quote     = Quote::factory()->create();
        $lineItems = QuoteItem::factory()->count(2)->make(['quote_id' => $quote->quote_id])->toArray();

        /**
         * @payload
         * {
         *    "quote_id": 1,
         *    "line_items": [
         *       {"product_id": 1, "quantity": 2, "price": 50},
         *       {"product_id": 2, "quantity": 3, "price": 75}
         *    ]
         * }
         */

        // Act
        $response = $this->putJson(route('api.quotes.update', ['quote' => $quote->quote_id]), [
            'line_items' => $lineItems,
        ]);

        // Assert
        $response->assertSuccessful();
        $this->assertDatabaseHas('quote_items', ['quote_id' => $quote->quote_id]);
    }


    public function it_deletes_a_quote(): void
    {
        $user   = User::factory()->create();
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

        $initialQuote = Quote::factory()->create([
            'invoice_id'       => $invoice->invoice_id,
            'user_id'          => $user->user_id,
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'quote_number'     => '::quote_number::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson(
            route('api.quotes.destroy', ['quote' => $initialQuote->quote_id])
        );

        $response->assertSuccessful();

        $getQuoteResponse = $this->getJson(
            route('api.quotes.show', [
                'quote' => $initialQuote->quote_id,
            ])
        );

        $getQuoteResponse->assertNotFound();
    }
    // endregion

    // region Custom Tests

    public function it_changes_the_client_of_a_quote(): void
    {
        $quote     = Quote::factory()->create();
        $newClient = Client::factory()->create();
        $payload   = ['client_id' => $newClient->id];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('api.filament.ivpl.resources.filament.resources.quotes.change_client', ['record' => $quote->id]), $payload);
        $response->assertSuccessful();
    }


    public function it_adds_a_product_to_a_quote(): void
    {
        $quote   = Quote::factory()->create();
        $product = Product::factory()->create();
        $payload = [
            'product_id' => $product->id,
            'quantity'   => 2,
            'price'      => $product->product_price,
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('api.filament.ivpl.resources.filament.resources.quotes.add_product', ['record' => $quote->id]), $payload);
        $response->assertSuccessful();
    }


    public function it_adds_a_task_to_a_quote(): void
    {
        $quote   = Quote::factory()->create();
        $task    = Task::factory()->create();
        $payload = [
            'task_id' => $task->id,
            'hours'   => 5,
            'rate'    => 100,
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('api.filament.ivpl.resources.filament.resources.quotes.add_task', ['record' => $quote->id]), $payload);
        $response->assertSuccessful();
    }
    // endregion

    // region Spicy Tests

    public function it_generates_a_quote_pdf(): void
    {
        $quote = Quote::factory()->create();

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('api.filament.ivpl.resources.filament.resources.quotes.generate_pdf', ['record' => $quote->id]));
        $response->assertSuccessful();
    }


    public function it_copies_a_quote_to_an_invoice(): void
    {
        // Payload for copying a quote to an invoice
        $quote = Quote::factory()->create();

        $payload = [
            'quote_id' => $quote->id,
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('api.filament.ivpl.resources.filament.resources.quotes.copy_to_invoice', ['record' => $quote->id]), $payload);
        $response->assertSuccessful();
    }


    public function it_clones_a_quote(): void
    {
        // Payload for cloning a quote
        $quote = Quote::factory()->create([
            'quote_number' => '::quote_number::',
            'quote_total'  => 500,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.quotes.clone', ['quote' => $quote->quote_id]));

        $response->assertSuccessful();
        $response->assertJsonFragment(['number' => '::quote_number:: - Copy']);
    }


    public function it_calculates_totals(): void
    {
        // Payload for calculating totals
        $quote = Quote::factory()->create([
            'quote_number' => '::quote_number::',
        ]);

        QuoteItem::factory()->count(3)->create([
            'quote_id'      => $quote->quote_id,
            'item_price'    => 100,
            'item_quantity' => 2,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('api.quotes.totals', ['quote' => $quote->quote_id]));
        $response->assertSuccessful();
        $response->assertJsonFragment(['total' => 600]);
    }
}
