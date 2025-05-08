<?php

namespace Modules\Core\Tests\Unit\Support;

use InvalidArgumentException;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Support\Drivers\Mollie;
use Modules\Payments\Support\Drivers\PayPal;
use Modules\Payments\Support\Drivers\Stripe;
use Modules\Payments\Support\MerchantFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class MerchantFactoryTest extends AbstractTestCase
{
    #[Test]
    #[Group('support')]
    public function it_resolves_mollie_driver(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $driver = MerchantFactory::make('mollie');
        $this->assertInstanceOf(Mollie::class, $driver);
    }

    #[Test]
    #[Group('support')]
    public function it_resolves_stripe_driver(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $driver = MerchantFactory::make('stripe');
        $this->assertInstanceOf(Stripe::class, $driver);
    }

    #[Test]
    #[Group('support')]
    public function it_resolves_paypal_driver(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $driver = MerchantFactory::make('paypal');
        $this->assertInstanceOf(PayPal::class, $driver);
    }

    #[Test]
    #[Group('support')]
    public function it_throws_for_invalid_driver(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->expectException(InvalidArgumentException::class);
        MerchantFactory::make('unknown');
    }
}
