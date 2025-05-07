<?php

namespace Modules\Payments\Tests\Unit\Support\MerchantDrivers;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class PayPalTest extends AbstractTestCase
{
    #[Test]
    #[Group('support')]
    public function it_returns_paypal_redirect_url(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $driver = new PayPal();
        $url    = $driver->getRedirectUrl(['amount' => 50]);

        $this->assertStringContainsString('paypal', $url);
    }
}
