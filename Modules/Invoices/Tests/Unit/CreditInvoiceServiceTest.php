<?php

namespace Modules\Invoices\Tests\Unit;

use Exception;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\CreditInvoiceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CreditInvoiceServiceTest extends AbstractTestCase
{
    /**
     * @payload ["invoiceId" => $invoice->id, "amount" => 50]
     */
    #[Test]
    #[Group('spicy')]
    public function it_applies_credit_to_invoice(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        $invoice = Invoice::factory()->create(['balance' => 100]);
        $service = new CreditInvoiceService();
        $credit  = $service->credit($invoice->id, 50);
        if (app()->isLocal()) {
            dump($credit);
        }
        $this->assertDatabaseHas('invoice_credits', ['invoice_id' => $invoice->id, 'amount' => 50]);
        $this->assertEquals(50, $credit->amount);
    }

    /**
     * @payload ["invoiceId" => $invoice->id, "amount" => 200]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_when_credit_exceeds_balance(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        $invoice = Invoice::factory()->create(['balance' => 100]);
        $service = new CreditInvoiceService();
        $this->expectException(Exception::class);
        $service->credit($invoice->id, 200);
    }
}
