<?php

namespace Modules\Payments\Services;

use Modules\Payments\Models\PaymentMethod;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Services\PaymentMethodService;

use InvalidArgumentException;
use Modules\Payments\Models\PaymentMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentMethodServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_method(): void
    {
        $method = (new PaymentMethodService())->create(['name' => 'Card']);
        $this->assertInstanceOf(PaymentMethod::class, $method);
    }

    #[Test]
    #[Group('services')]
    public function it_requires_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PaymentMethodService())->create([]);
    }
}
