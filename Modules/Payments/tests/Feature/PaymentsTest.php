<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Filament\Resources\PaymentResource\Pages\CreatePayment;
use Modules\Payments\Filament\Resources\PaymentResource\Pages\EditPayment;
use Modules\Payments\Filament\Resources\PaymentResource\Pages\ManagePayments;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;

class PaymentsTest extends AbstractTestCase
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
    public function it_shows_payments_index(): void
    {
        // $this->authenticate();
        $this->markTestIncomplete('payment date not formatted correctly');
        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Payment::factory()->create([
            'invoice_id'        => $paidInvoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_date'      => '2022-04-10',
            'payment_amount'    => 121,
        ]);

        Livewire::test(ManagePayments::class)
            ->assertStatus(200)
            ->assertSee('::payment_method_name::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_payment(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        Payment::factory()->create([
            'invoice_id'        => $paidInvoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_date'      => '2022-04-10',
            'payment_amount'    => 121,
        ]);

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(CreatePayment::class)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_save_payment_without_invoice_id(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(CreatePayment::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     **/
    public function it_payments_assign_method(): void
    {
        $this->markTestIncomplete();
        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Payment::factory()->create($payload);

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('assign')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.assign_method')
     *
     * @skip Not implemented yet
     **/
    public function it_fails_to_assign_payment_method_without_id(): void
    {
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('assign')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.process_refund')
     *
     * @skip Not implemented yet
     **/
    public function it_payments_process_refund(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     **/
    public function it_fails_to_process_refund_without_reason(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.process_partial_refund')
     *
     * @skip Not implemented yet
     **/
    public function it_payments_process_partial_refund(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.process_partial_refund')
     *
     * @skip Not implemented yet
     **/
    public function it_fails_to_process_partial_refund_without_payment_id(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.process')
     *
     * @skip Not implemented yet
     **/
    public function it_payments_process_partial_payment(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.process')
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_process_partial_payment_without_invoice_id(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.apply_method')
     *
     * @skip Not implemented yet
     **/
    public function it_fails_to_apply_payment_method_without_valid_method_id(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.mark_as_failed'
     *
     * @skip Not implemented yet
     **/
    public function it_payments_mark_as_failed(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        $payment = Payment::factory()->create($payload);

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.mark_as_failed')
     *
     * @skip Not implemented yet
     **/
    public function it_fails_to_mark_payment_as_failed_without_payment_id(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        $payment = Payment::factory()->create($payload);

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.assign_tax')
     *
     * @skip Not implemented yet
     **/
    public function it_payments_assign_tax(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $payload = [
            'invoice_id'        => $paidInvoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_date'      => '2022-04-10',
            'payment_amount'    => 121,
        ];
        $payment = Payment::factory()->create($payload);

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.payments.assign_tax')
     *
     * @skip Not implemented yet
     **/
    public function it_fails_to_assign_tax_without_tax_id(): void
    {
        $this->markTestIncomplete();
        // $this->authenticate();
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $payload = [
            'payment_date' => '2024-11-22',
            'amount'       => 100,
        ];

        $payment = Payment::factory()->create($payload);

        Livewire::test(ManagePayments::class)
            ->assertStatus(422)
            ->set('data.payment_date', $payload['payment_date'])
            ->set('data.payment_amount', $payload['amount'])
            ->call('processRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     */
    public function it_updates_a_payment(): void
    {
        $user = User::factory()->create();

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id'        => $paidInvoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_date'      => '2022-04-10',
            'payment_amount'    => 121,
        ]);

        $updatedData = [
            'payment_date' => now()->toDateString(),
            'amount'       => 2000,
            'payment_type' => 'Credit Card',
            'note'         => 'Final payment',
        ];

        Livewire::test(EditPayment::class, ['record' => $payment->payment_id])
            ->set('data.payment_date', $updatedData['payment_date'])
            ->set('data.payment_amount', $updatedData['amount'])
            ->set('data.payment_type', $updatedData['payment_type'])
            ->set('data.note', $updatedData['note'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', array_merge($updatedData, [
            'payment_id' => $payment->payment_id,
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_payment(): void
    {
        $this->markTestIncomplete('Needs delete action');

        $invoiceGroup = InvoiceGroup::factory()->create([
            'invoice_group_name'              => '::invoicegroup_name::',
            'invoice_group_identifier_format' => '::invoice_group_identifier_format::',
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $paidInvoice = Invoice::factory()->paid()->create([
            'invoice_group_id' => $invoiceGroup->invoice_group_id,
            'invoice_number'   => '::paid_invoice_number::',
            'payment_method'   => $paymentMethod->payment_method_id,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id'        => $paidInvoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_date'      => '2022-04-10',
            'payment_amount'    => 121,
        ]);

        Livewire::test(ManagePayments::class)
            ->callTableAction('delete', $payment)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('payments', [
            'payment_id' => $payment->payment_id,
        ]);
    }
    // endregion
}
