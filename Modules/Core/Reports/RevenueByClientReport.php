<?php

namespace Modules\Reports\Reports;

use Modules\Payments\Models\Payment;
use App\Support\CurrencyFormatter;

class RevenueByClientReport
{
    public function getResults($companyProfileId = null, $year)
    {
        $results = [];

        $payments = Payment::select('payments.*')
            ->with(['invoice.customer'])
            ->year($year)
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->orderBy('customers.name');

        if ($companyProfileId) {
            $payments->where('company_id', $companyProfileId);
        }

        $payments = $payments->get();

        foreach ($payments as $payment) {
            if (isset($results[$payment->invoice->customer->name]['months'][date('n', strtotime($payment->paid_at))])) {
                $results[$payment->invoice->customer->name]['months'][date('n', strtotime($payment->paid_at))] += $payment->amount / $payment->invoice->exchange_rate;
            } else {
                $results[$payment->invoice->customer->name]['months'][date('n', strtotime($payment->paid_at))] = $payment->amount / $payment->invoice->exchange_rate;
            }
        }

        foreach ($results as $client => $result) {
            $results[$client]['total'] = 0;

            foreach (range(1, 12) as $month) {
                if ( ! isset($results[$client]['months'][$month])) {
                    $results[$client]['months'][$month] = CurrencyFormatter::format(0);
                } else {
                    $results[$client]['total'] += $results[$client]['months'][$month];
                    $results[$client]['months'][$month] = CurrencyFormatter::format($results[$client]['months'][$month]);
                }
            }
            $results[$client]['total'] = CurrencyFormatter::format($results[$client]['total']);
        }

        return $results;
    }
}
