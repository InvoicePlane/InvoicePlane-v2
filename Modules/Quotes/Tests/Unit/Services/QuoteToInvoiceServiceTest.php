<?php

namespace Modules\Quotes\Tests\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteToInvoiceServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_converts_quote_to_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $quote = Quote::factory()->create();

        $invoice = (new QuoteToInvoiceService())->convert($quote);

        $this->assertNotNull($invoice->id);
    }
}
