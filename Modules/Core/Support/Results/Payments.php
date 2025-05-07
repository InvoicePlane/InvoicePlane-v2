<?php

namespace Modules\Core\Support\Results;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Models\Payment;

use Modules\Core\Support\Results\SourceInterface;


class Payments implements SourceInterface
{
    public function getResults($params = [])
    {
        $payment = Payment::select(
            'invoices.number',
            'payments.paid_at',
            'payments.amount',
            'payment_methods.name AS payment_method',
            'payments.note'
        )
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'payment_method_id')
            ->orderBy('invoices.number');

        return $payment->get()->toArray();
    }
}
