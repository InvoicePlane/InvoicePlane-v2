<?php

namespace Modules\Payments\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
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
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_payments(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
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

        Payment::factory()->for($this->company)->create($payload);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class, ['tenant' => Str::lower($this->company->search_code)]);

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('payments', [
            'invoice_id'     => $payload['invoice_id'],
            'customer_id'    => $payload['customer_id'],
            'payment_method' => $payload['payment_method'],
            'payment_amount' => $payload['payment_amount'],
            'paid_at'        => $payload['paid_at'] . ' 00:00:00',
        ]);
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
        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dd($component->errors());
            dd($payload);
        }*/

        /* Assert */
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge(
            $payload,
            [
                'payment_amount' => TestDecimal::exact(250),
                'paid_at'        => '2024-11-01 00:00:00',
            ]
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();

        $payload = [
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'payment_amount' => 250.00,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'paid_at'        => '2024-11-01',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['payment_amount']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('modals')]
    public function it_updates_a_payment_through_a_modal(): void
    {
        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payment = Payment::factory()
            ->for($this->company)
            ->create([
                'invoice_id'     => $invoice->id,
                'customer_id'    => $customer->id,
                'payment_method' => PaymentMethod::BANK_TRANSFER->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'payment_amount' => 123.00,
                'paid_at'        => '2024-11-01',
            ]);

        $payload = [
            'payment_status' => PaymentStatus::COMPLETED->value,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class, ['record' => $payment->id])
            ->mountAction(TestAction::make('edit')->table($payment), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payment_status' => PaymentStatus::COMPLETED->value]);
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
        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /*if (app()->runningUnitTests()) {
            dd($component->errors());
            dd($payload);
        }*/

        /* Assert */
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge(
            $payload,
            [
                'payment_amount' => TestDecimal::exact(250),
                'paid_at'        => '2024-11-01 00:00:00',
            ]
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();

        $payload = [
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_amount' => 250.00,
            'paid_at'        => '2024-11-01',
        ];

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'payment_amount' => 250.00,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* Assert */
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
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payload = [
            'invoice_id'     => $invoice->id,
            'customer_id'    => $customer->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'paid_at'        => '2024-11-01',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['payment_amount']);

        $this->assertDatabaseMissing('payments', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_payment(): void
    {
        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payment = Payment::factory()
            ->for($this->company)
            ->create([
                'invoice_id'     => $invoice->id,
                'customer_id'    => $customer->id,
                'payment_method' => PaymentMethod::BANK_TRANSFER->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'payment_amount' => 123.00,
                'paid_at'        => '2024-11-01',
            ]);

        $payload = ['payment_amount' => 888.00];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditPayment::class, ['record' => $payment->id])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'payment_amount' => 888.00]);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_payment(): void
    {
        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payment = Payment::factory()
            ->for($this->company)
            ->for($invoice)
            ->create([
                'customer_id'    => $customer->id,
                'payment_method' => PaymentMethod::BANK_TRANSFER->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'payment_amount' => 250.00,
                'paid_at'        => '2024-11-01',
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction(TestAction::make('delete')->table($payment))
            ->callMountedAction();
        $component->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_if_invoice_is_paid(): void
    {

        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $customer->id,
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::PAID->value,
        ]);

        $payment = Payment::factory()
            ->for($this->company)
            ->for($invoice)
            ->create([
                'customer_id'    => $customer->id,
                'payment_method' => PaymentMethod::BANK_TRANSFER->value,
                'payment_status' => PaymentStatus::COMPLETED->value,
                'payment_amount' => 250.00,
                'paid_at'        => '2024-11-01',
            ]);

        /** act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction(TestAction::make('delete')->table($payment))
            ->callMountedAction();

        /* Assert */
        $component
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_already_deleted_payment(): void
    {

        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        $payment = Payment::factory()
            ->for($this->company)
            ->for($invoice)
            ->create([
                'customer_id'    => $customer->id,
                'payment_method' => PaymentMethod::BANK_TRANSFER->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'payment_amount' => 250.00,
                'paid_at'        => '2024-11-01',
            ]);
        $payment->delete();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction(TestAction::make('delete')->table($payment))
            ->callMountedAction();

        /* Assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
