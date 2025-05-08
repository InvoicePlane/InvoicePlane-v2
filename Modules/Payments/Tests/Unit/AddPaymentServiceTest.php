<?php

namespace Modules\Payments\Tests\Unit;

use Exception;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Payments\Services\AddPaymentService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class AddPaymentServiceTest extends AbstractTestCase
{
    /**
     * @payload ["invoiceId"=>$invoice->id,"amount"=>100]
     */
    #[Test]
    #[Group('spicy')]
    public function it_adds_payment_to_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $invoice = Invoice::factory()->create(['balance' => 200]);
        $service = new AddPaymentService();
        $payment = $service->add($invoice->id, 100);
        if (app()->isLocal()) {
            dump($payment);
        }
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 100]);
        $this->assertInstanceOf(Payment::class, $payment);
    }

    /**
     * @payload ["invoiceId"=>$invoice->id,"amount"=>300]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_when_amount_exceeds_balance(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $invoice = Invoice::factory()->create(['balance' => 200]);
        $service = new AddPaymentService();
        $this->expectException(Exception::class);
        $service->add($invoice->id, 300);
    }
}
