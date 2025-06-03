<?php

namespace Modules\Payments\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Core\Support\NumberFormatter;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;

class PaymentService extends BaseService
{
    public function model(): string
    {
        return Payment::class;
    }

    public function createPayment(array $data): Model
    {
        $customerId = $data['customer_id'] ?? Invoice::query()->findOrFail($data['invoice_id'])->customer_id;

        $payment = $this->create([
            'customer_id'        => $customerId,
            'invoice_id'         => $data['invoice_id'] ?? null,
            'merchant_client_id' => $data['merchant_client_id'] ?? null,
            'payment_method'     => $data['payment_method'],
            'payment_status'     => PaymentStatus::PENDING->value,
            'payment_amount'     => NumberFormatter::formatTrimmed($data['payment_amount']),
            'paid_at'            => $data['paid_at'],
            'notes'              => $data['notes'] ?? null,
        ]);

        /* if ($payment->merchant_client_id) {
            dispatch(new ProcessMerchantPaymentJob($payment));
        } */

        return $payment;
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        $payment->fill([
            'payment_method' => $data['payment_method'],
            'payment_amount' => $data['payment_amount'],
            'paid_at'        => $data['paid_at'],
        ])->save();

        return $payment;
    }
}
