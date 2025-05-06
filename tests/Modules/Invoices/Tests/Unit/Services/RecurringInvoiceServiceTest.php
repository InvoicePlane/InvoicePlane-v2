<?php

namespace Modules\Invoices\Tests\Unit\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Invoices\Services\RecurringInvoiceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RecurringInvoiceServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_generates_invoice_from_template(): void
    {
        $invoice = RecurringInvoiceService::generateFrom(1);
        $this->assertInstanceOf(\Modules\Invoices\Models\Invoice::class, $invoice);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_if_template_is_missing(): void
    {
        $this->expectException(ModelNotFoundException::class);
        RecurringInvoiceService::generateFrom(999);
    }
}
