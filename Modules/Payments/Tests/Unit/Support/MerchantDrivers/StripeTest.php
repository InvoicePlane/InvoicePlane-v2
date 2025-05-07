<?php

namespace Modules\Payments\Tests\Unit\Support\MerchantDrivers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class StripeTest extends AbstractTestCase
{
    #[Test]
    #[Group('support')]
    public function it_builds_stripe_payment_form(): void
    {
        $driver = new Stripe();
        $url    = $driver->getFormUrl(['amount' => 100]);

        $this->assertStringContainsString('stripe', $url);
    }
}
