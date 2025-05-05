<?php

namespace App\IpModules\Reports\Reports;

use App\IpModules\Customers\Models\Customer;
use App\Support\CurrencyFormatter;
use App\Support\DateFormatter;

class ClientStatementReport
{
    public function getResults($clientName, $fromDate, $toDate, $companyProfileId = null)
    {
        $results = [
            'client_name' => '',
            'from_date'   => '',
            'to_date'     => '',
            'subtotal'    => 0,
            'discount'    => 0,
            'tax'         => 0,
            'total'       => 0,
            'paid'        => 0,
            'balance'     => 0,
            'records'     => [],
        ];

        $client = Customer::where('unique_name', $clientName)->first();

        $invoices = $client->invoices()
            ->with('items', 'customer.currency', 'amount.invoice.currency')
            ->notCanceled()
            ->where('invoiced_at', '>=', $fromDate)
            ->where('invoiced_at', '<=', $toDate)
            ->orderBy('invoiced_at');

        if ($companyProfileId) {
            $invoices->where('company_id', $companyProfileId);
        }

        $invoices = $invoices->get();

        foreach ($invoices as $invoice) {
            $results['records'][] = [
                'formatted_invoice_date' => $invoice->formatted_invoice_date,
                'number'                 => $invoice->number,
                'summary'                => $invoice->summary,
                'subtotal'               => $invoice->amount->subtotal,
                'discount'               => $invoice->amount->discount,
                'tax'                    => $invoice->amount->tax,
                'total'                  => $invoice->amount->total,
                'paid'                   => $invoice->amount->paid,
                'balance'                => $invoice->amount->balance,
                'formatted_subtotal'     => $invoice->amount->formatted_subtotal,
                'formatted_discount'     => $invoice->amount->formatted_discount,
                'formatted_tax'          => $invoice->amount->formatted_tax,
                'formatted_total'        => $invoice->amount->formatted_total,
                'formatted_paid'         => $invoice->amount->formatted_paid,
                'formatted_balance'      => $invoice->amount->formatted_balance,
            ];

            $results['subtotal'] += $invoice->amount->subtotal;
            $results['discount'] += $invoice->amount->discount;
            $results['tax'] += $invoice->amount->tax;
            $results['total'] += $invoice->amount->total;
            $results['paid'] += $invoice->amount->paid;
            $results['balance'] += $invoice->amount->balance;
        }

        $currency = $client->currency;

        $results['client_name'] = $client->name;
        $results['from_date']   = DateFormatter::format($fromDate);
        $results['to_date']     = DateFormatter::format($toDate);
        $results['subtotal']    = CurrencyFormatter::format($results['subtotal'], $currency);
        $results['discount']    = CurrencyFormatter::format($results['discount'], $currency);
        $results['tax']         = CurrencyFormatter::format($results['tax'], $currency);
        $results['total']       = CurrencyFormatter::format($results['total'], $currency);
        $results['paid']        = CurrencyFormatter::format($results['paid'], $currency);
        $results['balance']     = CurrencyFormatter::format($results['balance'], $currency);

        return $results;
    }
}
