<?php

namespace Modules\Quotes\Services;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Quotes\Services\QuoteCopyService;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;

use Modules\Quotes\Tests\Unit\QuoteCopyServiceTest;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteCopyServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_clones_a_quote(): void
    {
        $original = Quote::factory()->create(['number' => 'Q-Original']);
        $copy     = (new QuoteCopyService())->copy($original);

        $this->assertNotEquals($original->id, $copy->id);
        $this->assertTrue(str_contains($copy->number, 'COPY'));
    }
}
