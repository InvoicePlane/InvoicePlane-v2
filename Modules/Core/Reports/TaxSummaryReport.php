<?php

namespace Modules\Reports\Reports;

use Modules\Core\Support\DateFormatter;

use Modules\Core\Support\Statuses\InvoiceStatuses;

use Modules\Invoices\Models\Invoice;

use Modules\Expenses\Models\Expense;

use Modules\Core\Support\CurrencyFormatter;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Support\Results\Invoices;

use Modules\Core\Support\NumberFormatter;

use Modules\Core\Support\CurrencyFormatter;
use Modules\Core\Support\DateFormatter;
use Modules\Core\Support\NumberFormatter;
use Modules\Core\Support\Statuses\InvoiceStatuses;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;

class TaxSummaryReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null, $excludeUnpaidInvoices = 0)
    {
        $results = [
            'from_date' => DateFormatter::format($fromDate),
            'to_date'   => DateFormatter::format($toDate),
            'total'     => 0,
            'paid'      => 0,
            'remaining' => 0,
            'records'   => [],
        ];

        $invoices = Invoice::with(['items.taxRate', 'items.taxRate2', 'items.amount'])
            ->where('invoiced_at', '>=', $fromDate)
            ->where('invoiced_at', '<=', $toDate)
            ->where('invoice_status_id', '<>', InvoiceStatuses::getStatusId('canceled'));

        $expenseTax = (Expense::where('expensed_at', '>=', $fromDate)
            ->where('expensed_at', '<=', $toDate)
            ->sum('tax')) ?: 0;

        if ($companyProfileId) {
            $invoices->where('company_id', $companyProfileId);
        }

        if ($excludeUnpaidInvoices) {
            $invoices->paid();
        }

        $invoices = $invoices->get();

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $invoiceItem) {
                if ($invoiceItem->tax_rate_id) {
                    $key = $invoiceItem->taxRate->name . ' (' . NumberFormatter::format($invoiceItem->taxRate->percent, null, 3) . '%)';

                    if (isset($results['records'][$key]['taxable_amount'])) {
                        $results['records'][$key]['taxable_amount'] += $invoiceItem->amount->subtotal / $invoice->exchange_rate;
                        $results['records'][$key]['taxes'] += $invoiceItem->amount->tax_1 / $invoice->exchange_rate;
                    } else {
                        $results['records'][$key]['taxable_amount'] = $invoiceItem->amount->subtotal / $invoice->exchange_rate;
                        $results['records'][$key]['taxes']          = $invoiceItem->amount->tax_1 / $invoice->exchange_rate;
                    }
                }

                if ($invoiceItem->tax_rate_2_id) {
                    $key = $invoiceItem->taxRate2->name . ' (' . NumberFormatter::format($invoiceItem->taxRate2->percent, null, 3) . '%)';

                    if (isset($results['records'][$key]['taxable_amount'])) {
                        if ($invoiceItem->taxRate2->is_compound) {
                            $results['records'][$key]['taxable_amount'] += ($invoiceItem->amount->subtotal + $invoiceItem->amount->tax_1) / $invoice->exchange_rate;
                        } else {
                            $results['records'][$key]['taxable_amount'] += $invoiceItem->amount->subtotal / $invoice->exchange_rate;
                        }

                        $results['records'][$key]['taxes'] += $invoiceItem->amount->tax_2 / $invoice->exchange_rate;
                    } else {
                        if ($invoiceItem->taxRate2->is_compound) {
                            $results['records'][$key]['taxable_amount'] = ($invoiceItem->amount->subtotal + $invoiceItem->amount->tax_2) / $invoice->exchange_rate;
                        } else {
                            $results['records'][$key]['taxable_amount'] = $invoiceItem->amount->subtotal / $invoice->exchange_rate;
                        }

                        $results['records'][$key]['taxes'] = $invoiceItem->amount->tax_2 / $invoice->exchange_rate;
                    }
                }
            }
        }

        foreach ($results['records'] as $key => $result) {
            $results['total']                           = $results['total'] + $result['taxes'];
            $results['records'][$key]['taxable_amount'] = CurrencyFormatter::format($result['taxable_amount']);
            $results['records'][$key]['taxes']          = CurrencyFormatter::format($result['taxes']);
        }

        $results['paid']      = CurrencyFormatter::format($expenseTax);
        $results['remaining'] = CurrencyFormatter::format($results['total'] - $expenseTax);
        $results['total']     = CurrencyFormatter::format($results['total']);

        return $results;
    }
}
