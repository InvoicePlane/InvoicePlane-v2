<?php

namespace Modules\Core\Services\Migration\Support;

use Modules\Core\Services\Migration\MigrationContext;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;

class FinancialInvariantValidator
{
    /**
     * Validate financial invariants for all migrated invoices and quotes.
     *
     * @return array{
     *     passed: bool,
     *     invoices_checked: int,
     *     quotes_checked: int,
     *     passed_count: int,
     *     failed_count: int,
     *     mismatches: array<array{type: string, id: int, number: string, field: string, expected: float, actual: float}>
     * }
     */
    public function validate(MigrationContext $context): array
    {
        $mismatches      = [];
        $invoicesChecked = 0;
        $quotesChecked   = 0;
        $passedCount     = 0;
        $failedCount     = 0;

        // Validate Invoices
        $invoiceAmounts    = $context->getSourceTable('invoice_amounts')->keyBy('invoice_id');
        $createdInvoiceIds = $context->getCreatedIds(Invoice::class);

        foreach ($createdInvoiceIds as $v2InvoiceId) {
            $invoice = Invoice::withoutGlobalScopes()->find($v2InvoiceId);
            if ( ! $invoice) {
                continue;
            }

            // Find corresponding v1 ID
            $v1Id = null;
            foreach ($context->getSourceTable('invoices') as $v1Row) {
                if ($context->getId('invoices', $v1Row['invoice_id'] ?? null) === $v2InvoiceId) {
                    $v1Id = $v1Row['invoice_id'];
                    break;
                }
            }

            if ($v1Id === null || ! isset($invoiceAmounts[$v1Id])) {
                continue;
            }

            $v1Amount = $invoiceAmounts[$v1Id];
            $invoicesChecked++;
            $invoicePassed = true;

            $checks = [
                'invoice_item_subtotal' => [(float) ($v1Amount['invoice_item_subtotal'] ?? 0), (float) ($invoice->invoice_item_subtotal ?? 0)],
                'invoice_tax_total'     => [(float) ($v1Amount['invoice_tax_total'] ?? 0), (float) ($invoice->invoice_tax_total ?? 0)],
                'invoice_total'         => [(float) ($v1Amount['invoice_total'] ?? 0), (float) ($invoice->invoice_total ?? 0)],
                'invoice_paid'          => [(float) ($v1Amount['invoice_paid'] ?? 0), (float) ($invoice->payments()->sum('payment_amount') ?? 0)],
            ];

            // In v1, balance = invoice_total - invoice_paid
            $expectedBalance           = (float) ($v1Amount['invoice_balance'] ?? 0);
            $actualBalance             = (float) $invoice->invoice_total - (float) $invoice->payments()->sum('payment_amount');
            $checks['invoice_balance'] = [$expectedBalance, $actualBalance];

            foreach ($checks as $field => [$expected, $actual]) {
                if (abs($expected - $actual) > 0.01) {
                    $invoicePassed = false;
                    $mismatches[]  = [
                        'type'     => 'invoice',
                        'id'       => $invoice->id,
                        'number'   => (string) ($invoice->invoice_number ?? $invoice->id),
                        'field'    => $field,
                        'expected' => round($expected, 2),
                        'actual'   => round($actual, 2),
                    ];
                }
            }

            if ($invoicePassed) {
                $passedCount++;
            } else {
                $failedCount++;
            }
        }

        // Validate Quotes
        $quoteAmounts    = $context->getSourceTable('quote_amounts')->keyBy('quote_id');
        $createdQuoteIds = $context->getCreatedIds(Quote::class);

        foreach ($createdQuoteIds as $v2QuoteId) {
            $quote = Quote::withoutGlobalScopes()->find($v2QuoteId);
            if ( ! $quote) {
                continue;
            }

            $v1Id = null;
            foreach ($context->getSourceTable('quotes') as $v1Row) {
                if ($context->getId('quotes', $v1Row['quote_id'] ?? null) === $v2QuoteId) {
                    $v1Id = $v1Row['quote_id'];
                    break;
                }
            }

            if ($v1Id === null || ! isset($quoteAmounts[$v1Id])) {
                continue;
            }

            $v1Amount = $quoteAmounts[$v1Id];
            $quotesChecked++;
            $quotePassed = true;

            $checks = [
                'quote_item_subtotal' => [(float) ($v1Amount['quote_item_subtotal'] ?? 0), (float) ($quote->quote_item_subtotal ?? 0)],
                'quote_tax_total'     => [(float) ($v1Amount['quote_tax_total'] ?? 0), (float) ($quote->quote_tax_total ?? 0)],
                'quote_total'         => [(float) ($v1Amount['quote_total'] ?? 0), (float) ($quote->quote_total ?? 0)],
            ];

            foreach ($checks as $field => [$expected, $actual]) {
                if (abs($expected - $actual) > 0.01) {
                    $quotePassed  = false;
                    $mismatches[] = [
                        'type'     => 'quote',
                        'id'       => $quote->id,
                        'number'   => (string) ($quote->quote_number ?? $quote->id),
                        'field'    => $field,
                        'expected' => round($expected, 2),
                        'actual'   => round($actual, 2),
                    ];
                }
            }

            if ($quotePassed) {
                $passedCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'passed'           => empty($mismatches),
            'invoices_checked' => $invoicesChecked,
            'quotes_checked'   => $quotesChecked,
            'passed_count'     => $passedCount,
            'failed_count'     => $failedCount,
            'mismatches'       => $mismatches,
        ];
    }
}
