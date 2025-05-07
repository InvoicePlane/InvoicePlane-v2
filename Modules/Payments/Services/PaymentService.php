<?php

namespace Modules\Payments\Services;

use Modules\Payments\Services\PaymentService;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Models\Payment;

use Modules\Core\Services\BaseService;


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
