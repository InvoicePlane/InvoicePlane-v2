<?php

namespace Modules\Invoices\Tests\Feature;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Invoices\Models\Invoice;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\ListInvoices;

use Modules\Invoices\Enums\InvoiceStatus;

use Modules\Invoices\Tests\Feature\InvoicesTest;

use Modules\Core\Models\User;

use Modules\Core\Support\Results\Clients;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\EditInvoice;

use Modules\Core\Models\Company;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\CreateInvoice;

use Modules\Core\Support\Results\Invoices;

use Modules\Clients\Models\Relation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(InvoiceResource::class)]
class InvoicesTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withCompany()->create();
        session(['current_company_id' => $this->user->company_id]);
    }

    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['invoice_date' => '2024-11-01', 'invoice_number' => 'INV-0001']
     */
    public function it_lists_invoices(): void
    {
        // arrange
        $customer = Relation::factory()->for($this->user->company)->customer()->create();

        $invoice = Invoice::factory()->for($this->user->company)->create([
            'invoice_number' => 'INV-0001',
            'invoice_date'   => '2024-11-01',
            'customer_id'    => $customer->id,
        ]);

        // act + assert
        Livewire::test(ListInvoices::class)
            ->actingAs($this->user)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($invoice);
    }

    #[Test]
    public function it_creates_invoice_with_items(): void
    {
        /** @payload */
        $payload = [
            'customer_id'   => 1,
            'invoice_date'  => now()->format('Y-m-d'),
            'due_date'      => now()->addDays(14)->format('Y-m-d'),
            'invoice_items' => [
                ['name' => 'Service A', 'quantity' => 2, 'price' => 100],
            ],
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 10,
            'total'    => 230,
        ];

        Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'customer_id' => 1,
            'subtotal'    => 200,
            'tax'         => 40,
            'discount'    => 10,
            'total'       => 230,
        ]);

        $this->assertDatabaseCount('invoice_items', 1);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['invoice_date' => '2024-11-01', 'invoice_number' => 'INV-9000', 'customer_id' => 1, 'status' => 'draft']
     */
    public function it_creates_an_invoice(): void
    {
        // arrange
        $customer = Relation::factory()->for($this->user->company)->customer()->create();

        $payload = [
            'invoice_number' => 'INV-9000',
            'invoice_date'   => '2024-11-01',
            'customer_id'    => $customer->id,
            'status'         => InvoiceStatus::DRAFT,
        ];

        // act
        Livewire::test(CreateInvoice::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('invoices', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['invoice_date' => '2024-11-01']
     */
    public function it_fails_to_create_invoice_without_customer(): void
    {
        // arrange
        $payload = [
            'invoice_date' => '2024-11-01',
            // 'customer_id' => missing
        ];

        // act
        Livewire::test(CreateInvoice::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['invoice_number' => null]
     */
    public function it_fails_to_create_invoice_without_number(): void
    {
        // arrange
        $customer = Relation::factory()->for($this->user->company)->customer()->create();
        $payload  = [
            'invoice_date'   => '2024-11-01',
            'customer_id'    => $customer->id,
            'invoice_number' => null,
        ];

        // act
        Livewire::test(CreateInvoice::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['invoice_number']);
    }

    #[Test]
    public function it_fails_to_create_invoice_without_items(): void
    {
        /** @payload */
        $payload = [
            'customer_id'   => 1,
            'invoice_date'  => now()->format('Y-m-d'),
            'due_date'      => now()->addDays(14)->format('Y-m-d'),
            'invoice_items' => [],
            'subtotal'      => 0,
            'tax'           => 0,
            'discount'      => 0,
            'total'         => 0,
        ];

        Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['invoice_items']);
    }

    #[Test]
    public function it_fails_if_total_mismatch(): void
    {
        /** @payload */
        $payload = [
            'customer_id'   => 1,
            'invoice_date'  => now()->format('Y-m-d'),
            'due_date'      => now()->addDays(14)->format('Y-m-d'),
            'invoice_items' => [
                ['name' => 'Service A', 'quantity' => 2, 'price' => 100],
            ],
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 10,
            'total'    => 100, // deliberately wrong
        ];

        Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['total']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['status' => 'sent']
     */
    public function it_updates_an_invoice(): void
    {
        // arrange
        $invoice = Invoice::factory()->for($this->user->company)->create([
            'status' => InvoiceStatus::DRAFT,
        ]);

        $payload = ['status' => InvoiceStatus::SENT];

        // act
        Livewire::test(EditInvoice::class, ['record' => $invoice->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('invoices', [
            'id'     => $invoice->id,
            'status' => InvoiceStatus::SENT,
        ]);
    }

    #[Test]
    public function it_edits_invoice_and_updates_total(): void
    {
        $invoice = Invoice::factory()->for($this->user->company)->create([
            'subtotal' => 100,
            'tax'      => 20,
            'discount' => 0,
            'total'    => 120,
        ]);

        /** @payload */
        $payload = [
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 20,
            'total'    => 220,
        ];

        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'total' => 220]);
    }

    #[Test]
    public function it_fails_to_update_with_invalid_discount(): void
    {
        $invoice = Invoice::factory()->for($this->user->company)->create([
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 10,
            'total'    => 230,
        ]);

        /** @payload */
        $payload = [
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 9999, // absurd value
            'total'    => 230,
        ];

        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm($payload)
            ->call('save')
            ->assertHasErrors(['discount']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['status' => null]
     */
    public function it_fails_to_update_invoice_with_invalid_status(): void
    {
        // arrange
        $invoice = Invoice::factory()->for($this->user->company)->create();
        $payload = ['status' => null];

        // act
        Livewire::test(EditInvoice::class, ['record' => $invoice->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors(['status']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_an_invoice(): void
    {
        // arrange
        $invoice = Invoice::factory()->for($this->user->company)->create();

        // act
        Livewire::test(ListInvoices::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $invoice)
            ->assertHasNoErrors();

        // assert
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    public function it_fails_to_delete_paid_invoice(): void
    {
        $invoice = Invoice::factory()
            ->for($this->user->company)
            ->hasPayments(1)
            ->create([
                'status' => InvoiceStatus::PAID,
            ]);

        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->call('delete', $invoice->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    public function it_fails_to_delete_if_has_payments(): void
    {
        $invoice = Invoice::factory()
            ->for($this->user->company)
            ->hasPayments(1)
            ->create();

        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->call('delete', $invoice->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_invoice_that_was_already_deleted(): void
    {
        // arrange
        $invoice = Invoice::factory()->for($this->user->company)->create();
        $invoice->delete();

        // act + assert
        Livewire::test(ListInvoices::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $invoice)
            ->assertHasErrors();

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }
}
