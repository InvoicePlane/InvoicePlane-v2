<?php

namespace Modules\Payments\Services;

use InvalidArgumentException;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddPaymentServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_adds_a_payment(): void
    {
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
        $this->expectException(InvalidArgumentException::class);

        (new PaymentService())->add(['amount' => 100.00]);
    }
}
