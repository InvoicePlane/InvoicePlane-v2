<?php

namespace Modules\Core\Services\Import;

use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;

class PaymentsImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_payments'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId  = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['payments']);

        $this->importPayments();

        return $this->stats;
    }

    private function importPayments(): void
    {
        $payments = $this->getImportData('ip_payments');

        foreach ($payments as $v1Payment) {
            $invoiceId  = $this->idMappings['invoices'][$v1Payment->invoice_id] ?? null;
            $customerId = $this->idMappings['clients'][$v1Payment->client_id] ?? null;

            if ( ! $invoiceId || ! $customerId) {
                continue;
            }

            $payment = Payment::create([
                'company_id'     => $this->companyId,
                'customer_id'    => $customerId,
                'invoice_id'     => $invoiceId,
                'payment_number' => null,
                'payment_method' => $this->mapPaymentMethod($v1Payment->payment_method_id ?? 1)->value,
                'payment_status' => PaymentStatus::COMPLETED->value,
                'paid_at'        => $v1Payment->payment_date ?? now(),
                'payment_amount' => $v1Payment->payment_amount ?? 0,
                'notes'          => $v1Payment->payment_note ?? null,
            ]);

            $this->idMappings['payments'][$v1Payment->id] = $payment->id;
            $this->stats['payments']++;
        }
    }

    private function mapPaymentMethod(int $methodId): PaymentMethod
    {
        return match ($methodId) {
            1       => PaymentMethod::CASH,
            2       => PaymentMethod::BANK_TRANSFER,
            3       => PaymentMethod::CREDIT_CARD,
            4       => PaymentMethod::PAYPAL,
            default => PaymentMethod::BANK_TRANSFER,
        };
    }
}
