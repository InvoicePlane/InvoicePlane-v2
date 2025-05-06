<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Filament\Company\Resources\PaymentResource;
use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\CreatePayment;
use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\EditPayment;
use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\ListPayments;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PaymentResource::class)]
class PaymentsTest extends AbstractTestCase
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
     * @payload ['amount' => 500.00]
     */
    public function it_lists_payments(): void
    {
        // arrange
        $customer = Relation::factory()->for($this->user->company)->customer()->create();
        $payment  = Payment::factory()->for($this->user->company)->create([
            'customer_id' => $customer->id,
            'amount'      => 500.00,
        ]);

        // act + assert
        Livewire::test(ListPayments::class)
            ->actingAs($this->user)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($payment);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['amount' => 250.00, 'payment_method' => 'bank_transfer', 'paid_at' => '2024-11-01']
     */
    public function it_creates_a_payment(): void
    {
        // arrange
        $customer = Relation::factory()->for($this->user->company)->customer()->create();
        $invoice  = Invoice::factory()->for($this->user->company)->create();

        $payload = [
            'amount'         => 250.00,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'paid_at'        => '2024-11-01',
            'customer_id'    => $customer->id,
            'invoice_id'     => $invoice->id,
        ];

        // act
        Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoErrors();

        // assert
        $this->assertDatabaseHas('payments', [
            'amount'         => 250.00,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'customer_id'    => $customer->id,
            'invoice_id'     => $invoice->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['amount' => null]
     */
    public function it_fails_to_create_payment_without_amount(): void
    {
        // arrange
        $payload = ['amount' => null];

        // act
        Livewire::test(CreatePayment::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['amount']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['payment_method' => null]
     */
    public function it_fails_to_create_payment_without_method(): void
    {
        // arrange
        $payload = ['payment_method' => null];

        // act
        Livewire::test(CreatePayment::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['payment_method']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['amount' => 888.00]
     */
    public function it_updates_a_payment(): void
    {
        // arrange
        $payment = Payment::factory()->for($this->user->company)->create(['amount' => 123.00]);
        $payload = ['amount' => 888.00];

        // act
        Livewire::test(EditPayment::class, ['record' => $payment->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'amount' => 888.00]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['amount' => null]
     */
    public function it_fails_to_update_payment_with_null_amount(): void
    {
        // arrange
        $payment = Payment::factory()->for($this->user->company)->create();

        // act
        Livewire::test(EditPayment::class, ['record' => $payment->id])
            ->actingAs($this->user)
            ->fillForm(['amount' => null])
            ->call('save')
            ->assertHasFormErrors(['amount']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_a_payment(): void
    {
        // arrange
        $payment = Payment::factory()->for($this->user->company)->create();

        // act
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->call('delete', $payment->id)
            ->assertHasNoErrors();

        // assert
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    #[Test]
    public function it_fails_to_delete_if_invoice_is_paid(): void
    {
        $invoice = Invoice::factory()
            ->for($this->user->company)
            ->create(['status' => InvoiceStatus::PAID]);

        $payment = Payment::factory()
            ->for($this->user->company)
            ->for($invoice)
            ->create();

        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->call('delete', $payment->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_already_deleted_payment(): void
    {
        // arrange
        $payment = Payment::factory()->for($this->user->company)->create();
        $payment->delete();

        // act + assert
        Livewire::test(ListPayments::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $payment)
            ->assertHasErrors();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
