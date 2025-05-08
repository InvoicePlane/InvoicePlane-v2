<?php

namespace Modules\Payments\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class AddPaymentServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_adds_a_payment(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payment = (new PaymentService())->addPayment([
            'amount'     => 100.00,
            'invoice_id' => 1,
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
    }

    #[Test]
    #[Group('services')]
    public function it_requires_invoice_id(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->expectException(InvalidArgumentException::class);

        (new PaymentService())->add(['amount' => 100.00]);
    }
}
