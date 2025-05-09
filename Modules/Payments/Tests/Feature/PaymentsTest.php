<?php

namespace Modules\Payments\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentMethod as PaymentMethodEnum;
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
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        /*$this->user = User::factory()->withCompany()->create();
        session(['current_company_id' => $this->user->company_id]);*/
    }

    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['amount' => 500.00]
     */
    #[Group('crud')]
    public function it_lists_payments(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $customer = Relation::factory()->for($this->user->company)->customer()->create();
        $payment  = Payment::factory()->for($this->user->company)->create([
            'customer_id' => $customer->id,
            'amount'      => 500.00,
        ]);

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListPayments::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($payment);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_payment(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $customer = Relation::factory()->for($this->user->company)->customer()->create();
        $invoice  = Invoice::factory()->for($this->user->company)->create();

        $payload = [
            'amount'         => 250.00,
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER,
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
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER,
            'customer_id'    => $customer->id,
            'invoice_id'     => $invoice->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_payment_without_amount(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = ['amount' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreatePayment::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['amount']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_payment_without_method(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = ['payment_method' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreatePayment::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['payment_method']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_payment(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payment = Payment::factory()->for($this->user->company)->create(['amount' => 123.00]);
        $payload = ['amount' => 888.00];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditPayment::class, ['record' => $payment->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'amount' => 888.00]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_payment_with_null_amount(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payment = Payment::factory()->for($this->user->company)->create();

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditPayment::class, ['record' => $payment->id])->fillForm(['amount' => null])->call('save');

        /* assert */
        $component->assertHasFormErrors(['amount']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_payment(): void
    {
        $this->markTestIncomplete();
        /* arrange */

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
        $this->markTestIncomplete();

        /* arrange */

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
    public function it_fails_to_delete_already_deleted_payment(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payment = Payment::factory()->for($this->user->company)->create();
        $payment->delete();

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListPayments::class)->callTableAction('delete', $payment);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
