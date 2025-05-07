<?php

namespace Modules\Invoices\Tests\Unit;

use Modules\Invoices\Services\SumexService;

use Modules\Invoices\Tests\Unit\SumexServiceTest;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Support\Results\Invoices;

use Exception;
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

        $service = new SumexService();
        $this->expectException(Exception::class);
        $service->process(0);
    }
}
