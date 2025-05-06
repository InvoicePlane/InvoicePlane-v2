<?php

namespace Modules\Reports\Reports;

use Modules\Payments\Models\Payment;
use App\Support\CurrencyFormatter;
use App\Support\DateFormatter;

class PaymentsCollectedReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null)
    {
        $results = [
            'from_date' => DateFormatter::format($fromDate),
            'to_date'   => DateFormatter::format($toDate),
            'payments'  => [],
            'total'     => 0,
        ];

        $payments = Payment::select('payments.*')
            ->with(['invoice.customer', 'paymentMethod'])
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->dateRange($fromDate, $toDate);

        if ($companyProfileId) {
            $payments->where('invoices.company_id', $companyProfileId);
        }

        $payments = $payments->get();

        foreach ($payments as $payment) {
            $results['payments'][] = [
                'client_name'    => $payment->invoice->customer->name,
                'invoice_number' => $payment->invoice->number,
                'payment_method' => $payment->paymentMethod->name ?? '',
                'note'           => $payment->note,
                'date'           => $payment->formatted_paid_at,
                'amount'         => CurrencyFormatter::format($payment->amount / $payment->invoice->exchange_rate),
            ];

            $results['total'] += $payment->amount / $payment->invoice->exchange_rate;
        }

        $results['total'] = CurrencyFormatter::format($results['total']);

        return $results;
    }
}
