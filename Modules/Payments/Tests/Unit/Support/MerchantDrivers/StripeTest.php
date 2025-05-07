<?php

namespace Modules\Payments\Tests\Unit\Support\Drivers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StripeTest extends TestCase
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
