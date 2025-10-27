<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Support\Carbon;
use Modules\Core\Support\DateHelpers;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class DateHelpersTest extends AbstractTestCase
{
    #[Test]
    public function it_format_date_returns_formatted_date(): void
    {
        $this->markTestIncomplete();

        $date = Carbon::create(2025, 7, 14);
        $this->assertEquals('2025-07-14', DateHelpers::formatDate($date));
    }

    #[Test]
    public function it_format_date_returns_dash_for_null(): void
    {
        $this->markTestIncomplete();

        $this->assertEquals('-', DateHelpers::formatDate(null));
    }

    #[Test]
    public function it_format_since_returns_since_for_past_date(): void
    {
        $this->markTestIncomplete();

        $date   = now()->subDays(3);
        $result = DateHelpers::formatSince($date);
        $this->assertStringContainsString('ago', $result);
    }

    #[Test]
    public function it_format_since_returns_in_for_future_date(): void
    {
        $this->markTestIncomplete();

        $date   = now()->addDays(5);
        $result = DateHelpers::formatSince($date);
        $this->assertStringContainsString('in', $result);
    }

    #[Test]
    public function it_format_since_returns_date_for_large_difference(): void
    {
        $this->markTestIncomplete();

        $date   = now()->subDays(400);
        $result = DateHelpers::formatSince($date);
        $this->assertEquals(DateHelpers::formatDate($date), $result);
    }
}
