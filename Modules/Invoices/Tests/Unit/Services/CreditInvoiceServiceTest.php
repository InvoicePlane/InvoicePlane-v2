<?php

namespace Modules\Invoices\Tests\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use TypeError;

class CreditInvoiceServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_generates_credit_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $original = Invoice::factory()->create();
        $credit   = (new CreditInvoiceService())->createFrom($original);

        $this->assertTrue($credit->is_credit_note);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_with_invalid_input(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->expectException(TypeError::class);
        (new CreditInvoiceService())->createFrom(null);
    }
}
