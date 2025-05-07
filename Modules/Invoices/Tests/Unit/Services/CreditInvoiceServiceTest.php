<?php

namespace Modules\Invoices\Services;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Tests\Unit\CreditInvoiceServiceTest;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

class CreditInvoiceServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_generates_credit_invoice(): void
    {
        $original = Invoice::factory()->create();
        $credit   = (new CreditInvoiceService())->createFrom($original);

        $this->assertTrue($credit->is_credit_note);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_with_invalid_input(): void
    {
        $this->expectException(TypeError::class);
        (new CreditInvoiceService())->createFrom(null);
    }
}
