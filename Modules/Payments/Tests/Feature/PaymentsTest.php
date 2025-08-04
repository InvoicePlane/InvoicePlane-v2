<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Core\Tests\TestDecimal;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Filament\Company\Resources\Payments\Pages\CreatePayment;
use Modules\Payments\Filament\Company\Resources\Payments\Pages\EditPayment;
use Modules\Payments\Filament\Company\Resources\Payments\Pages\ListPayments;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListPayments::class)]
class PaymentsTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_payments(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        Payment::factory()->for($company)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class, ['tenant' => Str::lower($this->user->companies()->first()->search_code)]);

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('payments', $payload);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('modals')]
    /**
     * @payload
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_status": "pending",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_creates_a_payment_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->customer()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        if (app()->isLocal()) {
            dd($component->errors());
            dd($payload);
        }

        /* assert */
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge(
            $payload,
            ['payment_amount' => TestDecimal::exact(250)]
        ));
    }

    #[Test]
    #[Group('modals')]
    /**
     * @payload missing: invoice_id
     * {
     *   "customer_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_status": "pending",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_through_a_modal_without_required_invoice_id(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        $payload = [
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['invoice_id']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('modals')]
    /**
     * @payload missing: payment_method
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_status": "pending",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_through_a_modal_without_required_payment_method(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($this->user->companies()->first())->create();

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        if (app()->isLocal()) {
            dump($payload);
        }

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['payment_method']);
        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('modals')]
    /**
     * @payload missing: payment_status
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_through_a_modal_without_required_payment_status(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
            'notes'          => 'Test payment',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component->assertHasFormErrors(['payment_status']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('modals')]
    /**
     * @payload missing: paid_at
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_status": "pending",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_through_a_modal_without_required_paid_at(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create(['customer_id' => $customer->id]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'payment_amount' => 250.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component->assertHasFormErrors(['paid_at']);
        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('modals')]
    /**
     * @payload missing: payment_amount
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_status": "pending",
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_through_a_modal_without_required_amount(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'paid_at'        => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['payment_amount']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('modals')]
    public function it_updates_a_payment_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $payment = Payment::factory()->for($this->user->companies()->first())->create(['payment_amount' => 123.00]);
        $payload = ['payment_amount' => 888.00];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class, ['record' => $payment->id])
            ->mountAction('edit', ['record' => $payment->id])
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payment_amount' => 888.00]);
    }

    #[Test]
    #[Group('modals')]
    public function it_fails_to_update_payment_through_a_modal_without_required_amount(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->customer()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payment = Payment::factory()->for($this->user->companies()->first())->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class, ['record' => $payment->id])
            ->mountAction('edit', ['record' => $payment->id])
            ->fillForm(['payment_amount' => null])
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['payment_amount']);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_status": "pending",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_creates_a_payment(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->customer()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dd($component->errors());
            dd($payload);
        }

        /* assert */
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge(
            $payload,
            ['payment_amount' => TestDecimal::exact(250)]
        ));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: invoice_id
     * {
     *   "customer_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_status": "pending",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_without_required_invoice_id(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['invoice_id']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: payment_method
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_status": "pending",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_without_required_payment_method(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        if (app()->isLocal()) {
            dump($payload);
        }

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['payment_method']);
        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: payment_status
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_without_required_payment_status(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
            'notes'          => 'Test payment',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component->assertHasFormErrors(['payment_status']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: paid_at
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_status": "pending",
     *   "payment_amount": 250.00,
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_without_required_paid_at(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'payment_amount' => 250.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component->assertHasFormErrors(['paid_at']);
        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: payment_amount
     * {
     *   "customer_id": 1,
     *   "invoice_id": 1,
     *   "payment_method": "bank_transfer",
     *   "payment_status": "pending",
     *   "paid_at": "2024-11-01"
     * }
     */
    public function it_fails_to_create_payment_without_required_amount(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Relation::factory()->for($company)->create();
        $invoice  = Invoice::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'paid_at'        => '2024-11-01',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['payment_amount']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_payment(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $payment = Payment::factory()->for($this->user->companies()->first())->create(['payment_amount' => 123.00]);
        $payload = ['payment_amount' => 888.00];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditPayment::class, ['record' => $payment->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payment_amount' => 888.00]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_payment_with_null_amount(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $payment = Payment::factory()->for($this->user->companies()->first())->create();

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditPayment::class, ['record' => $payment->id])->fillForm(['payment_amount' => null])->call('save');

        /* assert */
        $component->assertHasFormErrors(['payment_amount']);
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
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('delete', $payment);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_payments_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s payment.');
    }
    # endregion

    #region spicy
    # endregion
}
