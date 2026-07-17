<?php

namespace Modules\Payments\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Core\Support\NumberFormatter;
use Modules\Invoices\Enums\InvoiceStatus;
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

    /**
     * Record a payment against an invoice and keep the invoice status in sync:
     * fully paid invoices become Paid, partly paid Sent/Viewed invoices become
     * Partially Paid (Overdue invoices stay Overdue until settled in full).
     */
    public function enterInvoicePayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            /** @var Payment $payment */
            $payment = $this->createPayment([
                'customer_id'    => $invoice->customer_id,
                'invoice_id'     => $invoice->id,
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_status'] ?? PaymentStatus::COMPLETED->value,
                'payment_amount' => $data['payment_amount'],
                'paid_at'        => $data['paid_at'],
                'notes'          => $data['notes'] ?? null,
            ]);

            $this->syncInvoiceStatus($invoice);

            return $payment;
        });
    }

    /**
     * The open balance of an invoice: total minus the sum of its payments.
     */
    public function amountOwed(Invoice $invoice): float
    {
        $paid = (float) $invoice->payments()->sum('payment_amount');

        return max(round((float) $invoice->invoice_total - $paid, 4), 0.0);
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

    protected function syncInvoiceStatus(Invoice $invoice): void
    {
        if ($this->amountOwed($invoice) <= 0.0) {
            $invoice->update(['invoice_status' => InvoiceStatus::PAID]);

            return;
        }

        if (in_array($invoice->invoice_status, [InvoiceStatus::SENT, InvoiceStatus::VIEWED], true)) {
            $invoice->update(['invoice_status' => InvoiceStatus::PARTIALLY_PAID]);
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
}
