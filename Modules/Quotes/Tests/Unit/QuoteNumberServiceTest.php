<?php

namespace Modules\Quotes\Tests\Unit;

use Exception;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Services\QuoteNumberService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteNumberServiceTest extends AbstractTestCase
{
    /**
     * @payload ["groupId"=>$group->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_generates_quote_number(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        $group   = DocumentGroup::factory()->create(['left_pad' => 'QUO', 'next_number' => 5]);
        $service = new QuoteNumberService();
        $num     = $service->generate($group->id);
        if (app()->isLocal()) {
            dump($num);
        }
        $this->assertStringStartsWith('QTE-5', $num);
    }

    /**
     * @payload ["groupId"=>0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_for_invalid_group(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        $service = new QuoteNumberService();
        $this->expectException(Exception::class);
        $service->generate(0);
    }
}
