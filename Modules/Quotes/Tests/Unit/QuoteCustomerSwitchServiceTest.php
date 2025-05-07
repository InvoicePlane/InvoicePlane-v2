<?php

namespace Modules\Quotes\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Quotes\Services\QuoteCustomerSwitchService;

use Modules\Quotes\Tests\Unit\QuoteCustomerSwitchServiceTest;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;

use Modules\Core\Support\Results\Clients;

use Modules\Clients\Models\Relation;

use Exception;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteCustomerSwitchService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteCustomerSwitchServiceTest extends AbstractTestCase
{
    /**
     * @payload ["quoteId"=>$quote->id,"newClientId"=>$cust->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_switches_quote_client(): void
    {
        $this->markTestIncomplete();

        $quote    = Quote::factory()->create();
        $cust     = Relation::factory()->create();
        $service  = new QuoteCustomerSwitchService();
        $switched = $service->switch($quote->id, $cust->id);
        if (app()->isLocal()) {
            dump($switched);
        }
        $this->assertEquals($cust->id, $switched->client_id);
    }

    /**
     * @payload ["quoteId"=>0,"newClientId"=>0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_for_invalid_ids(): void
    {
        $this->markTestIncomplete();

        $service = new QuoteCustomerSwitchService();
        $this->expectException(Exception::class);
        $service->switch(0, 0);
    }
}
