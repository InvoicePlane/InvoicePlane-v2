<?php

namespace Modules\Invoices\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvoiceServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_an_invoice(): void
    {
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
        $this->expectException(InvalidArgumentException::class);

        (new InvoiceService())->create([
            'customer_id' => 1,
        ]);
    }
}
