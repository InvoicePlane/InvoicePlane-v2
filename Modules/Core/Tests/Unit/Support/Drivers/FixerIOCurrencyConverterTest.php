<?php

namespace Modules\Core\Support\Drivers;

use InvalidArgumentException;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class FixerIOCurrencyConverterTest extends AbstractTestCase
{
    #[Test]
    #[Group('support')]
    public function it_converts_currency(): void
    {
        $driver = new FixerIOCurrencyConverter();
        $amount = $driver->convert(100, 'EUR', 'USD');

        $this->assertIsNumeric($amount);
    }

    #[Test]
    #[Group('support')]
    public function it_fails_with_invalid_input(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $driver = new FixerIOCurrencyConverter();
        $driver->convert(100, 'EUR', '');
    }
}
