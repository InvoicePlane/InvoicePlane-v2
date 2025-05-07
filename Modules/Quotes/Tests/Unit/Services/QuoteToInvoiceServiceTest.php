<?php

namespace Modules\Quotes\Services;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;

use Modules\Quotes\Services\QuoteToInvoiceService;

use Modules\Quotes\Tests\Unit\QuoteToInvoiceServiceTest;

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
        $quote = Quote::factory()->create();

        $invoice = (new QuoteToInvoiceService())->convert($quote);

        $this->assertNotNull($invoice->id);
    }
}
