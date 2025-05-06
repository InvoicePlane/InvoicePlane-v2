<?php

namespace Modules\Core\Tests\Unit\Helpers;

use Carbon\CarbonPeriod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PeriodsHelperTest extends TestCase
{
    #[Test]
    public function it_creates_a_monthly_period(): void
    {
        // act
        $period = PeriodsHelper::monthly('2024-01-01', '2024-03-01');

        // assert
        $this->assertInstanceOf(CarbonPeriod::class, $period);
        $this->assertCount(3, $period->toArray());
    }

    #[Test]
    public function it_returns_empty_period_for_invalid_dates(): void
    {
        // act
        $period = PeriodsHelper::monthly('invalid', 'wrong');

        // assert
        $this->assertEmpty($period->toArray());
    }
}
