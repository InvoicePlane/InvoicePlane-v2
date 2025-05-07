<?php

namespace Modules\Payments\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Services\PaymentMethodService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class PaymentMethodServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_method(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $method = (new PaymentMethodService())->create(['name' => 'Card']);
        $this->assertInstanceOf(PaymentMethod::class, $method);
    }

    #[Test]
    #[Group('services')]
    public function it_requires_name(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->expectException(InvalidArgumentException::class);
        (new PaymentMethodService())->create([]);
    }
}
