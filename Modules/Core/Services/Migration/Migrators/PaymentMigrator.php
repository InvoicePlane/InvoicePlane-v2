<?php

namespace Modules\Core\Services\Migration\Migrators;

use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\MigrationContext;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;
use Throwable;

class PaymentMigrator implements EntityMigratorInterface
{
    public function name(): string
    {
        return 'payments';
    }

    public function label(): string
    {
        return 'Payments';
    }

    public function inspect(MigrationContext $context): array
    {
        $payments    = $context->getSourceTable('payments');
        $notes       = [];
        $willMigrate = 0;
        $unmappable  = 0;

        foreach ($payments as $row) {
            $invoiceId = $row['invoice_id'] ?? null;
            if ( ! $invoiceId) {
                $unmappable++;
                $notes[] = "Payment row #{$row['payment_id']} has no invoice_id, will be skipped.";
            } else {
                $willMigrate++;
            }
        }

        return [
            'source_count' => $payments->count(),
            'will_migrate' => $willMigrate,
            'unmappable'   => $unmappable,
            'notes'        => $notes,
        ];
    }

    public function migrate(MigrationContext $context): array
    {
        $payments       = $context->getSourceTable('payments');
        $paymentMethods = $context->getSourceTable('payment_methods')->keyBy('payment_method_id');

        $migrated = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($payments as $row) {
            $v1Id        = $row['payment_id'] ?? null;
            $v1InvoiceId = $row['invoice_id'] ?? null;
            $v1MethodId  = $row['payment_method_id'] ?? null;

            $v2InvoiceId = $context->getId('invoices', $v1InvoiceId);
            if ( ! $v2InvoiceId) {
                $errors[] = "Payment #{$v1Id} skipped: invoice #{$v1InvoiceId} not migrated.";
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                $migrated++;
                continue;
            }

            try {
                $invoice = Invoice::withoutGlobalScopes()->find($v2InvoiceId);
                if ( ! $invoice) {
                    $errors[] = "Payment #{$v1Id} skipped: invoice ID {$v2InvoiceId} not found in database.";
                    $skipped++;
                    continue;
                }

                $methodName = $paymentMethods[$v1MethodId]['payment_method_name'] ?? '';
                $methodEnum = $this->resolvePaymentMethod($methodName);

                $amount = (float) ($row['payment_amount'] ?? 0.0);
                $paidAt = ! empty($row['payment_date']) ? $row['payment_date'] : now();
                $note   = ! empty($row['payment_note']) ? (string) $row['payment_note'] : null;

                $payment = Payment::create([
                    'company_id'     => $context->getCompanyId(),
                    'customer_id'    => $invoice->customer_id,
                    'invoice_id'     => $invoice->id,
                    'payment_number' => 'PAY-' . mb_str_pad((string) ($v1Id ?? rand(100, 9999)), 5, '0', STR_PAD_LEFT),
                    'payment_method' => $methodEnum,
                    'payment_status' => PaymentStatus::COMPLETED,
                    'paid_at'        => $paidAt,
                    'payment_amount' => $amount,
                    'notes'          => $note,
                ]);

                $context->recordCreated(Payment::class, $payment->id);
                if ($v1Id !== null) {
                    $context->mapId('payments', $v1Id, $payment->id);
                }

                $migrated++;
            } catch (Throwable $e) {
                $errors[] = "Failed to migrate payment #{$v1Id}: " . $e->getMessage();
                $skipped++;
            }
        }

        $context->log("Migrated {$migrated} payments ({$skipped} skipped).");

        return [
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    public function rollback(MigrationContext $context): int
    {
        $paymentIds = $context->getCreatedIds(Payment::class);
        if (empty($paymentIds)) {
            return 0;
        }

        return Payment::withoutGlobalScopes()
            ->whereIn('id', $paymentIds)
            ->where('company_id', $context->getCompanyId())
            ->delete();
    }

    protected function resolvePaymentMethod(string $name): PaymentMethod
    {
        $normalized = mb_strtolower(mb_trim($name));

        if (str_contains($normalized, 'cash')) {
            return PaymentMethod::CASH;
        }
        if (str_contains($normalized, 'card') || str_contains($normalized, 'credit') || str_contains($normalized, 'debit')) {
            return PaymentMethod::CREDIT_CARD;
        }
        if (str_contains($normalized, 'paypal')) {
            return PaymentMethod::PAYPAL;
        }
        if (str_contains($normalized, 'stripe')) {
            return PaymentMethod::STRIPE;
        }

        return PaymentMethod::BANK_TRANSFER;
    }
}
