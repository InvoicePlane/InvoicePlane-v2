<?php

namespace Modules\Invoices\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoiceServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_an_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = (new InvoiceService())->create([
            'number'      => 'INV-2025-001',
            'customer_id' => 1,
        ]);

        $this->assertInstanceOf(Invoice::class, $invoice);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_without_number(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->expectException(InvalidArgumentException::class);

        (new InvoiceService())->create([
            'customer_id' => 1,
        ]);
    }
}
