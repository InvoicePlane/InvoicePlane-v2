<?php

namespace Modules\Payments\Tests\Unit;

use Modules\Payments\Services\PaymentService;

use Modules\Core\Support\Results\Payments;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Payments\Tests\Unit\PaymentValidationServiceTest;

use Exception;
use InvalidArgumentException;
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

        $service = new PaymentService();
        $result  = $service->validate(100, 200);
        if (app()->isLocal()) {
            dump($result);
        }
        $this->assertTrue($result);
    }

    #[Test]
    #[Group('spicy')]
    public function it_validates_required_fields(): void
    {
        $validated = (new PaymentService())->validate([
            'amount'     => 42.0,
            'invoice_id' => 1,
        ]);

        $this->assertArrayHasKey('amount', $validated);
    }

    #[Test]
    #[Group('spicy')]
    public function it_fails_without_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PaymentService())->validate(['invoice_id' => 1]);
    }

    /**
     * @payload ["amount"=>300,"balance"=>200]
     */
    #[Test]
    #[Group('spicy')]
    public function it_fails_validation_for_excess_amount(): void
    {
        $this->markTestIncomplete();

        $service = new PaymenService();
        $this->expectException(Exception::class);
        $service->validate(300, 200);
    }
}
