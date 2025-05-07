<?php

namespace Modules\Quotes\Tests\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteCustomerSwitchService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteCustomerSwitchServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_switches_customer(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $quote = Quote::factory()->create(['customer_id' => 1]);
        $newId = 2;

        (new QuoteCustomerSwitchService())->switch($quote, $newId);

        $this->assertEquals($newId, $quote->customer_id);
    }
}
