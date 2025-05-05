<?php

namespace Modules\Payments\Tests\Unit;

use Exception;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Services\PaymentValidationService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class PaymentValidationServiceTest extends AbstractTestCase
{
    /**
     * @payload ["amount"=>100,"balance"=>200]
     */
    #[Test]
    #[Group('spicy')]
    public function it_validates_payment_amount_successfully(): void
    {
        $this->markTestIncomplete();

        $service = new PaymentValidationService();
        $result  = $service->validate(100, 200);
        if (app()->isLocal()) {
            dump($result);
        }
        $this->assertTrue($result);
    }

    /**
     * @payload ["amount"=>300,"balance"=>200]
     */
    #[Test]
    #[Group('spicy')]
    public function it_fails_validation_for_excess_amount(): void
    {
        $this->markTestIncomplete();

        $service = new PaymentValidationService();
        $this->expectException(Exception::class);
        $service->validate(300, 200);
    }
}
