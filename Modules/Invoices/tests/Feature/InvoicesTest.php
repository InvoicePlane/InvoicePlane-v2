<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Filament\Resources\InvoiceResource\Pages\ManageInvoices;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Services\InvoiceService;
use Modules\Payments\Models\PaymentMethod;
use Modules\Products\Models\Product;
use Modules\Projects\Models\Task;

class InvoicesTest extends AbstractTestCase
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

    /** @test */
    public function it_shows_invoices_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'client_name' => '::client_name::',
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Invoice::factory()->create([
            'client_id'        => $client->client_id,
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Livewire::test(ManageInvoices::class)
            ->assertSee('::invoice_number::')
            ->assertSee('::invoicegroup_name::')
            ->assertSee('::payment_method_name::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_draft_invoices_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Invoice::factory()->draft()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::draft_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Invoice::factory()->sent()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::sent_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.invoices.index', ['status' => 'draft']));
        $response->assertStatus(200);
        $response->assertSee('::draft_invoice_number::');
        $response->assertDontSee('::sent_invoice_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_sent_invoices_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();
        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Invoice::factory()->draft()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::draft_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Invoice::factory()->sent()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::sent_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.invoices.index', ['status' => 'sent']));
        $response->assertStatus(200);
        $response->assertSee('::sent_invoice_number::');
        $response->assertDontSee('::draft_invoice_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_viewed_invoices_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Invoice::factory()->draft()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::draft_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Invoice::factory()->viewed()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::viewed_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.invoices.index', ['status' => 'viewed']));
        $response->assertStatus(200);
        $response->assertSee('::viewed_invoice_number::');
        $response->assertDontSee('::draft_invoice_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_paid_invoices_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Invoice::factory()->draft()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::draft_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.invoices.index', ['status' => 'paid']));
        $response->assertStatus(200);
        $response->assertSee('::paid_invoice_number::');
        $response->assertDontSee('::draft_invoice_number::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_overdue_invoices_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Invoice::factory()->draft()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::draft_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Invoice::factory()->overdue()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::inactive_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.invoices.index', ['status' => 'inactive']));
        $response->assertStatus(200);
        $response->assertSee('::inactive_invoice_number::');
        $response->assertDontSee('::active_invoice_number::');
    }

    /**
     * @test
     */
    public function it_shows_all_invoices_index(): void
    {
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Invoice::factory()->draft()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::draft_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Invoice::factory()->sent()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::sent_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Livewire::test(ManageInvoices::class)
            ->assertSee('::draft_invoice_number::')
            ->assertSee('::sent_invoice_number::');
    }

    // CRUD region
    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * @payload
     * {
     *    "client_id": 1,
     *    "invoice_date": "2024-11-22",
     *    "due_date": "2024-12-22",
     *    "status": "draft",
     *    "items": [
     *        {
     *            "product_id": 5,
     *            "quantity": 2,
     *            "price": 100
     *        }
     *    ]
     * }
     */
    public function it_can_create_an_invoice(): void
    {
        $this->markTestSkipped();

        // Arrange
        $client = Client::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'client_id'    => $client->client_id,
            'invoice_date' => '2024-11-22',
            'due_date'     => '2024-12-22',
            'status'       => 'draft',
            'items'        => [
                [
                    'product_id' => $product->product_id,
                    'quantity'   => 2,
                    'price'      => $product->price,
                ],
            ],
        ];

        Livewire::test(CreateInvoice::class)
            ->set('data.client_id', $payload['client_id'])
            ->set('data.invoice_date', $payload['invoice_date'])
            ->set('data.due_date', $payload['due_date'])
            ->set('data.status', $payload['status'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', ['client_id' => $payload['client_id']]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * @payload
     * {
     *    "client_id": 1,
     *    "invoice_date": "2024-11-22",
     *    "due_date": "2024-12-22",
     *    "status": "draft",
     *    "items": [
     *        {
     *            "product_id": 5,
     *            "quantity": 2,
     *            "price": 100
     *        }
     *    ]
     * }
     */
    public function it_can_edit_an_invoice(): void
    {
        $this->markTestSkipped();

        // Arrange
        $client = Client::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'client_id'    => $client->client_id,
            'invoice_date' => '2024-11-22',
            'due_date'     => '2024-12-22',
            'status'       => 'draft',
            'items'        => [
                [
                    'product_id' => $product->product_id,
                    'quantity'   => 2,
                    'price'      => $product->price,
                ],
            ],
        ];

        $updatedData = [
            'invoice_number' => '::draft_invoice_number::',
            'client_id'      => $client->client_id,
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->addDays(10)->toDateString(),
            'status'         => 'paid',
        ];

        Livewire::test(EditInvoice::class)
            ->set('data.client_id', $payload['client_id'])
            ->set('data.invoice_date', $payload['invoice_date'])
            ->set('data.due_date', $payload['due_date'])
            ->set('data.status', $payload['status'])
            ->call('save')
            ->assertHasNoErrors();

        $response->assertStatus(200);
        $this->assertDatabaseHas('invoices', array_merge($updatedData, [
            'invoice_id' => $invoice->invoice_id,
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * @payload
     * {
     * }
     */
    public function it_deletes_an_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'invoice_number' => '::draft_invoice_number::',
        ]);

        Livewire::test(ManageInvoices::class)
            ->callTableAction('delete', $invoice)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('invoices', [
            'invoice_id' => $invoice->invoice_id,
        ]);
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "client_id": 1,
     *    "invoice_date": "2024-11-22",
     *    "due_date": "2024-12-22",
     *    "status": "draft"
     * }
     */
    public function it_fails_to_create_an_invoice_without_required_fields(): void
    {
        $this->markTestSkipped();

        // Arrange
        $client = Client::factory()->create();

        $payload = [
            'client_id'    => $client->client_id,
            'invoice_date' => '2024-11-22',
            'status'       => 'draft',
            'items'        => [
                [
                    'product_id' => $product->product_id,
                    'quantity'   => 2,
                    'price'      => $product->price,
                ],
            ],
        ];

        Livewire::test(CreateInvoice::class)
            ->set('data.client_id', $payload['client_id'])
            ->set('data.invoice_date', $payload['invoice_date'])
            ->set('data.status', $payload['status'])
            ->call('create')
            ->assertHasNoErrors();

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_id']);
    }

    // endregion

    // region Spicy

    /** @test */
    public function it_projects_process_task_selections(): void
    {
        // $this->authenticate();
        $tasks = Task::factory()->count(3)->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.tasks.process_task_selections'), [
            'task_ids' => $tasks->pluck('task_id')->toArray(),
        ]);

        $response->assertStatus(200);

        foreach ($tasks as $task) {
            $this->assertDatabaseHas('tasks', [
                'task_id' => $task->task_id,
            ]);
        }
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "invoice_id": 1
     * }
     */
    public function it_can_generate_a_pdf_for_an_invoice(): void
    {
        $this->markTestSkipped();

        // Arrange
        $invoice = Invoice::factory()->create();

        // Act
        $response = $this->get(route('filament.ivpl.resources.filament.resources.invoices.generate-pdf', $invoice->invoice_id));

        // Assert
        $response->assertStatus(200);
        $this->assertFileExists(storage_path("app/invoices/{$invoice->invoice_id}.pdf"));
    }

    /** @test */
    public function it_fails_to_generate_a_pdf_for_a_non_existent_invoice(): void
    {
        $this->markTestSkipped();

        // Act
        $response = $this->get(route('filament.ivpl.resources.filament.resources.invoices.generate-pdf', 9999));

        // Assert
        $response->assertStatus(404);
    }

    // endregion

    // region Custom Tests
    /** @test */
    public function it_calculates_invoice_totals_correctly(): void
    {
        $this->markTestSkipped();

        // Arrange
        $invoice = Invoice::factory()->hasItems(3)->create(); // Assuming hasItems creates related invoice items.

        // Act
        $total = app(InvoiceService::class)->calculateTotals($invoice);

        // Assert
        $this->assertEquals(500, $total['subtotal']);
        $this->assertEquals(50, $total['tax']);
        $this->assertEquals(550, $total['total']);
    }
}
