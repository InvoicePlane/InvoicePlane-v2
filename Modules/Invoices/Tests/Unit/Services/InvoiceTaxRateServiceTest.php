<?php

namespace Modules\Invoices\Services;

use Exception;
use Modules\Core\Services\InvoiceTaxRateService;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoiceTaxRateServiceTest extends AbstractTestCase
{
    /**
     * @payload ["invoiceId"=>$invoice->id,"rateId"=>$rate->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_applies_tax_rate_to_invoice(): void
    {
        $this->markTestIncomplete();

        $invoice = Invoice::factory()->create();
        $rate    = TaxRate::factory()->create(['percent' => 10]);
        $service = new InvoiceTaxRateService();
        $applied = $service->apply($invoice->id, $rate->id);
        if (app()->isLocal()) {
            dump($applied);
        }
        $this->assertDatabaseHas('invoice_tax', ['invoice_id' => $invoice->id, 'tax_rate_id' => $rate->id]);
    }

    /**
     * @payload ["invoiceId"=>0,"rateId"=>0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_on_invalid_input(): void
    {
        $this->markTestIncomplete();

        $service = new InvoiceTaxRateService();
        $this->expectException(Exception::class);
        $service->apply(0, 0);
    }
}
