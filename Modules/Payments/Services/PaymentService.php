<?php

namespace Modules\Payments\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Core\Support\NumberFormatter;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;
use Throwable;

class PaymentService extends BaseService
{
    public function model(): string
    {
        return Payment::class;
    }

    public function createPayment(array $data): Model
    {
        $paymentData = $this->preparePaymentData($data);

        $payment = Payment::query()->create($paymentData);

        /* if ($payment->merchant_client_id) {
            dispatch(new ProcessMerchantPaymentJob($payment));
        } */

        return $payment;
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        DB::beginTransaction();

        try {
            $paymentData = $this->preparePaymentData($data);
            $payment->update($paymentData);

            DB::commit();

            return $payment;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function preparePaymentData(array $data): array
    {
        $customerId = $data['customer_id'] ?? $this->getCustomerIdFromInvoice($data['invoice_id']);

        return [
            'customer_id'        => $customerId,
            'invoice_id'         => $data['invoice_id'] ?? null,
            'merchant_client_id' => $data['merchant_client_id'] ?? null,
            'payment_method'     => $data['payment_method'],
            'payment_status'     => $data['payment_status'] ?? PaymentStatus::PENDING->value,
            'payment_amount'     => NumberFormatter::formatTrimmed($data['payment_amount']),
            'paid_at'            => $data['paid_at'],
            'notes'              => $data['notes'] ?? null,
        ];
    }

    protected function getCustomerIdFromInvoice(int $invoiceId): int
    {
        return Invoice::query()->findOrFail($invoiceId)->customer_id;
    }

    public function deletePayment(Payment $payment): Payment
    {
        DB::beginTransaction();
        try {
            $payment->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $payment;
    }
}
