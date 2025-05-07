<?php

namespace Modules\Quotes\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Quotes\Services\QuoteCopyService;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;

use Modules\Quotes\Tests\Unit\QuoteCopyServiceTest;

use Exception;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteCopyService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteCopyServiceTest extends AbstractTestCase
{
    /**
     * @payload ["quoteId"=>$quote->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_copies_a_quote(): void
    {
        $this->markTestIncomplete();

        $quote   = Quote::factory()->create(['status' => 'draft']);
        $service = new QuoteCopyService();
        $copy    = $service->copy($quote->id);
        if (app()->isLocal()) {
            dump($copy);
        }
        $this->assertDatabaseHas('quotes', ['original_id' => $quote->id]);
    }

    /**
     * @payload ["quoteId"=>0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_for_invalid_quote(): void
    {
        $this->markTestIncomplete();

        $service = new QuoteCopyService();
        $this->expectException(Exception::class);
        $service->copy(0);
    }
}
