<?php

namespace Modules\Payments\Services;

use Modules\Core\Services\BaseService;
use Modules\Payments\Models\Payment;

class PaymentService extends BaseService
{
    public function model(): string
    {
        return Payment::class;
    }

    public function create(array $validatedInput): Payment
    {
        $payment = new Payment($validatedInput);

        $payment->save();

        //event(new PaymentWasCreated($payment));

        return $payment;
    }
}
