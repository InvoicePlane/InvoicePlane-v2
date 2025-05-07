<?php

namespace Modules\Payments\Services;

use Modules\Payments\Services\PaymentService;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Models\Payment;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('services')]
class PaymentServiceTest extends TestCase
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
