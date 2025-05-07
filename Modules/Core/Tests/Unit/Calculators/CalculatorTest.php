<?php

namespace Modules\Core\Support\Calculators;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]

class CalculatorTest extends TestCase
{
    #[Test]
    #[Group('support')]
    public function it_adds_and_subtracts_correctly(): void
    {
        $calc = new Calculator();
        $calc->add(100);
        $calc->subtract(25);

        $this->assertEquals(75, $calc->total());
    }

    #[Test]
    #[Group('support')]
    public function it_resets_total(): void
    {
        $calc = new Calculator();
        $calc->add(50);
        $calc->reset();

        $this->assertEquals(0, $calc->total());
    }
}
