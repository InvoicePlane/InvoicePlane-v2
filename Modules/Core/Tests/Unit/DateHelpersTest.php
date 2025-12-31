<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Support\Carbon;
use Modules\Core\Support\DateHelpers;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class DateHelpersTest extends AbstractTestCase
{
    #[Test]
    public function it_format_date_returns_formatted_date(): void
    {
        /* arrange */
        $date = Carbon::create(2025, 7, 14);

        /* act */
        $result = DateHelpers::formatDate($date);

        /* assert */
        $this->assertEquals('2025-07-14', $result);
    }

    #[Test]
    public function it_format_date_returns_dash_for_null(): void
    {
        /* arrange */
        $date = null;

        /* act */
        $result = DateHelpers::formatDate($date);

        /* assert */
        $this->assertEquals('-', $result);
    }

    #[Test]
    public function it_format_since_returns_since_for_past_date(): void
    {
        /* arrange */
        $date = now()->subDays(3);

        /* act */
        $result = DateHelpers::formatSince($date);

        /* assert */
        $this->assertStringContainsString('ago', $result);
    }

    #[Test]
    #[Group('failed')]
    public function it_format_since_returns_in_for_future_date(): void
    {
        /* arrange */
        $date = now()->addDays(5);

        /* act */
        $result = DateHelpers::formatSince($date);

        /* assert */
        $this->assertStringContainsString('from now', $result);
    }

    #[Test]
    public function it_format_since_returns_date_for_large_difference(): void
    {
        /* arrange */
        $date = now()->subDays(400);

        /* act */
        $result = DateHelpers::formatSince($date);

        /* assert */
        $this->assertEquals(DateHelpers::formatDate($date), $result);
    }
}
