<?php

namespace Modules\Core\Tests\Unit\Calculators;

use Modules\Core\Support\Calculators\Calculator;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Calculator::class)]

class CalculatorTest extends AbstractTestCase
{
    #[Test]
    #[Group('support')]
    public function it_adds_and_subtracts_correctly(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$calc = new Calculator();
        //$calc->add(100);
        //$calc->subtract(25);

        //$this->assertEquals(75, $calc->total());
    }

    #[Test]
    #[Group('support')]
    public function it_resets_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$calc = new Calculator();
        //$calc->add(50);
        //$calc->reset();

        //$this->assertEquals(0, $calc->total());
    }
}
