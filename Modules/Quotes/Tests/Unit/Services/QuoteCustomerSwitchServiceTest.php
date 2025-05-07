<?php

namespace Modules\Quotes\Services;

use Modules\Quotes\Models\Quote;
use Modules\Quotes\Tests\Unit\QuoteCustomerSwitchServiceTest;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class QuoteCustomerSwitchServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_switches_customer(): void
    {
        $quote = Quote::factory()->create(['customer_id' => 1]);
        $newId = 2;

        (new QuoteCustomerSwitchService())->switch($quote, $newId);

        $this->assertEquals($newId, $quote->customer_id);
    }
}
