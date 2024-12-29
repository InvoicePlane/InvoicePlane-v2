<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Enums\PaymentStatus;
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
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /**
     * @test
     */
    public function it_shows_payments_index(): void
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

        Payment::factory()->create([
            'invoice_id'        => $paidInvoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_date'      => '2022-04-10',
            'payment_amount'    => 121,
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('payments.index'));
        $response->assertStatus(200);
        $response->assertSee('::payment_method_name::');
        $response->assertSee('10-04-2022');
        $response->assertSee(121);
    }

    /** @test */
    public function it_payments_save(): void
    {
        // $this->authenticate();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.store'), [
            'invoice_id'        => $invoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_amount'    => 100,
            'payment_date'      => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'invoice_id'        => $invoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_amount'    => 100,
        ]);
    }

    /** @test */
    public function it_fails_to_save_payment_without_invoice_id(): void
    {
        // $this->authenticate();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.store'), [
            'payment_method_id' => PaymentMethod::factory()->create()->payment_method_id,
            'payment_amount'    => 100,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_payments_assign_method(): void
    {
        // $this->authenticate();
        $payment = Payment::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.assign_method'), [
            'payment_id'        => $payment->payment_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'payment_id'        => $payment->payment_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
        ]);
    }

    /** @test */
    public function it_fails_to_assign_payment_method_without_id(): void
    {
        // $this->authenticate();
        $payment = Payment::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.assign_method'), [
            'payment_id' => $payment->payment_id,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_payments_process_refund(): void
    {
        // $this->authenticate();
        $payment = Payment::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.process_refund', [
            'payment_id'    => $payment->payment_id,
            'refund_reason' => 'Duplicate payment',
        ]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('refunds', [
            'payment_id'    => $payment->payment_id,
            'refund_reason' => 'Duplicate payment',
        ]);
    }

    /** @test */
    public function it_fails_to_process_refund_without_reason(): void
    {
        // $this->authenticate();
        $payment = Payment::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.process_refund', [
            'payment_id' => $payment->payment_id,
        ]));

        $response->assertStatus(422);
    }

    /** @test */
    public function it_payments_process_partial_refund(): void
    {
        // $this->authenticate();
        $payment = Payment::factory()->create();
        $refundAmount = 50.00;

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.process_partial_refund'), [
            'payment_id'    => $payment->payment_id,
            'refund_amount' => $refundAmount,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'payment_id'    => $payment->payment_id,
            'status'        => PaymentStatus::STATUS_REFUNDED_PARTIALLY,
            'refund_amount' => $refundAmount,
        ]);
    }

    /** @test */
    public function it_fails_to_process_partial_refund_without_payment_id(): void
    {
        // $this->authenticate();
        $refundAmount = 50.00;

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.process_partial_refund'), [
            'refund_amount' => $refundAmount,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_payments_process_partial_payment(): void
    {
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.process'), [
            'invoice_id'        => $invoice->invoice_id,
            'payment_amount'    => 50.00,
            'payment_method_id' => $paymentMethod->payment_method_id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->invoice_id,
        ]);
    }

    /** @test */
    public function it_fails_to_process_partial_payment_without_invoice_id(): void
    {
        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.process'), [
            'payment_amount' => 50.00,
        ]);

        $response->assertStatus(422);  // Missing invoice_id should result in validation failure
    }

    /** @test */
    public function it_fails_to_apply_payment_method_without_valid_method_id(): void
    {
        $invoice = Invoice::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.apply_method'), [
            'invoice_id'        => $invoice->invoice_id,
            'payment_method_id' => 9999, // Invalid payment method ID
        ]);

        $response->assertStatus(422); // Expecting validation error for invalid payment method ID
    }

    /** @test */
    public function it_payments_mark_as_failed(): void
    {
        // $this->authenticate();
        $payment = Payment::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.mark_as_failed', [
            'payment_id' => $payment->payment_id,
        ]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'payment_id' => $payment->payment_id,
            'status'     => PaymentStatus::STATUS_FAILED,
        ]);
    }

    /** @test */
    public function it_fails_to_mark_payment_as_failed_without_payment_id(): void
    {
        // $this->authenticate();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.mark_as_failed'));

        $response->assertStatus(422);
    }

    /** @test */
    public function it_payments_assign_tax(): void
    {
        $this->markTestSkipped('Not yet implemented');

        $payment = Payment::factory()->create();
        $taxRate = TaxRate::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.assign_tax'), [
            'payment_id'  => $payment->payment_id,
            'tax_rate_id' => $taxRate->tax_rate_id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'payment_id'  => $payment->payment_id,
            'tax_rate_id' => $taxRate->tax_rate_id,
        ]);
    }

    /** @test */
    public function it_fails_to_assign_tax_without_tax_id(): void
    {
        // $this->authenticate();
        $payment = Payment::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payments.assign_tax'), [
            'payment_id' => $payment->payment_id,
        ]);

        $response->assertStatus(422);
    }
}
