<?php

namespace Modules\Payments\Tests\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('services')]
class PaymentServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_fetches_payment_by_id(): void
    {
        $payment = Payment::factory()->create();
        $found   = (new PaymentService())->find($payment->id);

        $this->assertEquals($payment->id, $found->id);
    }

    #[Test]
    #[Group('services')]
    public function it_returns_null_for_unknown(): void
    {
        $this->assertNull((new PaymentService())->find(99999));
    }
}
