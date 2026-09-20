<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Core\Models\Setting;
use Modules\Core\Support\DateHelpers;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DateHelpers::class)]
class DateHelpersTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_format_date_returns_formatted_date(): void
    {
        /* Arrange */
        $date = Carbon::create(2025, 7, 14);

        /* Act */
        $result = DateHelpers::formatDate($date);

        /* Assert */
        $this->assertEquals('2025-07-14', $result);
    }

    #[Test]
    public function it_format_date_falls_back_to_default_format_when_setting_is_unset(): void
    {
        /* Arrange */
        $date = Carbon::create(2025, 7, 14);

        /* Act */
        $result = DateHelpers::formatDate($date);

        /* Assert */
        $this->assertEquals('2025-07-14', $result);
    }

    #[Test]
    public function it_format_date_honours_the_configured_date_format_setting(): void
    {
        /* Arrange */
        Setting::saveByKey('date_format', 'd/m/Y');
        $date = Carbon::create(2025, 7, 14);

        /* Act */
        $result = DateHelpers::formatDate($date);

        /* Assert */
        $this->assertEquals('14/07/2025', $result);
    }

    #[Test]
    public function it_format_date_falls_back_to_default_format_when_setting_is_blank(): void
    {
        /* Arrange */
        Setting::saveByKey('date_format', '');
        $date = Carbon::create(2025, 7, 14);

        /* Act */
        $result = DateHelpers::formatDate($date);

        /* Assert */
        $this->assertEquals('2025-07-14', $result);
    }

    #[Test]
    public function it_format_date_returns_dash_for_null(): void
    {
        /* Arrange */
        $date = null;

        /* Act */
        $result = DateHelpers::formatDate($date);

        /* Assert */
        $this->assertEquals('-', $result);
    }

    #[Test]
    public function it_format_since_returns_since_for_past_date(): void
    {
        /* Arrange */
        $date = now()->subDays(3);

        /* Act */
        $result = DateHelpers::formatSince($date);

        /* Assert */
        $this->assertStringContainsString('ago', $result);
    }

    #[Test]
    public function it_format_since_returns_in_for_future_date(): void
    {
        /* Arrange */
        $date = now()->addDays(5);

        /* Act */
        $result = DateHelpers::formatSince($date);

        /* Assert */
        $this->assertStringContainsString('from now', $result);
    }

    #[Test]
    public function it_format_since_returns_date_for_large_difference(): void
    {
        /* Arrange */
        $date = now()->subDays(400);

        /* Act */
        $result = DateHelpers::formatSince($date);

        /* Assert */
        $this->assertEquals(DateHelpers::formatDate($date), $result);
    }
}
