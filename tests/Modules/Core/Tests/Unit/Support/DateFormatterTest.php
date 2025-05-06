<?php

namespace Modules\Core\Tests\Unit\Support;

use InvalidArgumentException;
use Modules\Core\Support\DateFormatter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DateFormatterTest extends TestCase
{
    #[Test]
    public function it_formats_date_correctly(): void
    {
        $this->assertEquals('2025-05-06', DateFormatter::formatDate('2025-05-06'));
    }

    #[Test]
    public function it_throws_exception_on_invalid_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DateFormatter::formatDate('not-a-date');
    }

    #[Test]
    public function it_formats_null_as_empty_string(): void
    {
        $this->assertEquals('', DateFormatter::formatDate(null));
    }

    #[Test]
    public function it_returns_date_unchanged_if_already_formatted(): void
    {
        $this->assertEquals('2025-05-06', DateFormatter::formatDate('2025-05-06'));
    }

    #[Test]
    public function it_fails_on_numeric_timestamp(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DateFormatter::formatDate(1680000000);
    }
}
