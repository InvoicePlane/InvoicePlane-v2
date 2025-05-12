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
use Modules\Payments\Models\PaymentMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PaymentResource::class)]
class PaymentsTest extends AbstractTestCase
{
    protected User $user;

    #[Test]
    #[Group('smoke')]
    public function it_lists_payments(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        $payload = [
            'amount'         => 250.00,
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER,
            'paid_at'        => '2024-11-01',
            'customer_id'    => $customer->id,
            'invoice_id'     => $invoice->id,
        ];
        $payment = Payment::factory()->for($company)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class);

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_payment(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER,
            'amount'         => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        $component
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: invoice_id
     * {
     * 'payment_method_id' => 1,
     * 'paid_at' => '2025-01-01',
     * 'amount' => 500.00
     * }
     */
    public function it_fails_to_create_payment_without_required_customer_id(): void
    {
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        /* arrange */
        $payload = [
            'invoice_id'     => $invoice->id,
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER,
            'amount'         => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['customer_id']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: invoice_id
     * {
     *   "payment_method_id": 1,
     *   "paid_at": "2025-05-11",
     *   "amount": 100
     * }
     */
    public function it_fails_to_create_payment_without_required_invoice_id(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);
        $method   = PaymentMethod::factory()->for($this->user->companies()->first())->create();

        $payload = [
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER,
            'amount'         => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('createPayment');

        /* assert */
        $component->assertHasFormErrors(['invoice_id']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: payment_method_id
     * {
     *   "invoice_id": 1,
     *   "paid_at": "2025-05-11",
     *   "amount": 100
     * }
     */
    public function it_fails_to_create_payment_without_required_payment_method(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($this->user->companies()->first())->create();

        $payload = [
            'invoice_id'  => $invoice->id,
            'customer_id' => $customer->id,
            'amount'      => 250.00,
            'paid_at'     => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('createPayment');

        /* assert */
        $component->assertHasFormErrors(['payment_method_id']);
        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: paid_at
     * {
     *   "invoice_id": 1,
     *   "payment_method_id": 1,
     *   "amount": 100
     * }
     */
    public function it_fails_to_create_payment_without_required_paid_at(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER,
            'amount'         => 250.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('createPayment');

        /* assert */
        $component->assertHasFormErrors(['paid_at']);
        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: amount
     * {
     *   "invoice_id": 1,
     *   "payment_method_id": 1,
     *   "paid_at": "2025-05-11"
     * }
     */
    public function it_fails_to_create_payment_without_required_amount(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethodEnum::BANK_TRANSFER,
            'paid_at'        => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('createPayment');

        /* assert */
        $component->assertHasFormErrors(['amount']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_payment(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payment = Payment::factory()->for($this->user->companies()->first())->create(['amount' => 123.00]);
        $payload = ['amount' => 888.00];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditPayment::class, ['record' => $payment->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'amount' => 888.00]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_payment_with_null_amount(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payment = Payment::factory()->for($this->user->companies()->first())->create();

        /* act */
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

        $payment = Payment::factory()->for($this->user->companies()->first())->create();

        /* act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->call('delete', $payment->id)
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_if_invoice_is_paid(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()
            ->for($this->user->companies()->first())
            ->create(['status' => InvoiceStatus::PAID]);

        $payment = Payment::factory()
            ->for($this->user->companies()->first())
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

        $payment = Payment::factory()->for($this->user->companies()->first())->create();
        $payment->delete();

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListPayments::class)->callTableAction('delete', $payment);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
