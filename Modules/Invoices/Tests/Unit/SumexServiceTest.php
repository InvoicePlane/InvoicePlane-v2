<?php

namespace Modules\Invoices\Tests\Unit;

use Exception;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\SumexService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class SumexServiceTest extends AbstractTestCase
{
    /**
     * @payload ["invoiceId" => $invoice->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_processes_sumex_for_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->create();
        $service = new SumexService();
        $output  = $service->process($invoice->id);
        if (app()->isLocal()) {
            dump($output);
        }
        $this->assertArrayHasKey('sumex_code', $output);
    }

    /**
     * @payload ["invoiceId" => 0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_for_invalid_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $service = new SumexService();
        $this->expectException(Exception::class);
        $service->process(0);
    }
}
