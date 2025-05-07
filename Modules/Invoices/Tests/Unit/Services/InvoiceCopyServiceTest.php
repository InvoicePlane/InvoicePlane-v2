<?php

namespace Modules\Invoices\Services;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Invoices\Models\Invoice;

use Modules\Invoices\Services\InvoiceCopyService;

use Modules\Core\Support\Results\Invoices;

use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoiceCopyServiceTest extends AbstractTestCase
{
    /**
     * @payload ["invoiceId" => $invoice->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_copies_an_invoice(): void
    {
        $this->markTestIncomplete();

        $invoice = Invoice::factory()->create(['status' => 'draft']);
        $service = new InvoiceCopyService();
        $copy    = $service->copy($invoice->id);
        if (app()->isLocal()) {
            dump($copy);
        }
        $this->assertDatabaseHas('invoices', ['original_id' => $invoice->id]);
        $this->assertEquals($invoice->amount, $copy->amount);
    }

    /**
     * @payload ["invoiceId" => 0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_for_nonexistent_invoice(): void
    {
        $this->markTestIncomplete();

        $service = new InvoiceCopyService();
        $this->expectException(Exception::class);
        $service->copy(0);
    }
}
