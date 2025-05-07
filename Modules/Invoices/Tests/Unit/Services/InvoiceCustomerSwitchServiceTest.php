<?php

namespace Modules\Invoices\Services;

use Exception;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoiceCustomerSwitchServiceTest extends AbstractTestCase
{
    /**
     * @payload ["invoiceId"=>$invoice->id,"newClientId"=>$new->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_switches_invoice_client(): void
    {
        $this->markTestIncomplete();

        $invoice  = Invoice::factory()->create();
        $new      = Relation::factory()->create();
        $service  = new InvoiceCustomerSwitchService();
        $switched = $service->switch($invoice->id, $new->id);
        if (app()->isLocal()) {
            dump($switched);
        }
        $this->assertEquals($new->id, $switched->client_id);
    }

    /**
     * @payload ["invoiceId"=>0,"newClientId"=>0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_for_invalid_ids(): void
    {
        $this->markTestIncomplete();

        $service = new InvoiceCustomerSwitchService();
        $this->expectException(Exception::class);
        $service->switch(0, 0);
    }
}
