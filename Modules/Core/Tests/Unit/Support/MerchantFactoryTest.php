<?php

namespace Modules\Core\Support;

use InvalidArgumentException;
use Modules\Core\Support\Drivers\Mollie;
use Modules\Core\Support\Drivers\PayPal;
use Modules\Core\Support\Drivers\Stripe;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MerchantFactoryTest extends TestCase
{
    #[Test]
    #[Group('support')]
    public function it_resolves_mollie_driver(): void
    {
        $driver = MerchantFactory::make('mollie');
        $this->assertInstanceOf(Mollie::class, $driver);
    }

    #[Test]
    #[Group('support')]
    public function it_resolves_stripe_driver(): void
    {
        $driver = MerchantFactory::make('stripe');
        $this->assertInstanceOf(Stripe::class, $driver);
    }

    #[Test]
    #[Group('support')]
    public function it_resolves_paypal_driver(): void
    {
        $driver = MerchantFactory::make('paypal');
        $this->assertInstanceOf(PayPal::class, $driver);
    }

    #[Test]
    #[Group('support')]
    public function it_throws_for_invalid_driver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MerchantFactory::make('unknown');
    }
}
