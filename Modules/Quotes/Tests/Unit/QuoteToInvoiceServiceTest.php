<?php

namespace Modules\Quotes\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;

use Modules\Quotes\Services\QuoteToInvoiceService;

use Modules\Quotes\Tests\Unit\QuoteToInvoiceServiceTest;

use Modules\Core\Support\Results\Invoices;

use Exception;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteToInvoiceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteToInvoiceServiceTest extends AbstractTestCase
{
    /**
     * @payload ["quoteId"=>$quote->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_converts_quote_to_invoice(): void
    {
        $this->markTestIncomplete();

        $quote   = Quote::factory()->create(['total' => 500]);
        $service = new QuoteToInvoiceService();
        $invoice = $service->convert($quote->id);
        if (app()->isLocal()) {
            dump($invoice);
        }
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertDatabaseHas('invoices', ['amount' => 500]);
    }

    /**
     * @payload ["quoteId"=>0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_for_invalid_quote(): void
    {
        $this->markTestIncomplete();

        $service = new QuoteToInvoiceService();
        $this->expectException(Exception::class);
        $service->convert(0);
    }
}
