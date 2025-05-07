<?php

namespace Modules\Payments\Tests\Unit\Support\Drivers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PayPalTest extends TestCase
{
    #[Test]
    #[Group('support')]
    public function it_returns_paypal_redirect_url(): void
    {
        $driver = new PayPal();
        $url    = $driver->getRedirectUrl(['amount' => 50]);

        $this->assertStringContainsString('paypal', $url);
    }
}
