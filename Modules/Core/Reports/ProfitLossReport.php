<?php

namespace App\IpModules\Reports\Reports;

use App\IpModules\Expenses\Models\Expense;
use App\IpModules\Payments\Models\Payment;
use App\Support\CurrencyFormatter;
use App\Support\DateFormatter;

class ProfitLossReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null, $includeProfitBasedOn = 'invoiced_at')
    {
        $results = [
            'from_date'      => DateFormatter::format($fromDate),
            'to_date'        => DateFormatter::format($toDate),
            'income'         => 0,
            'total_expenses' => 0,
            'net_income'     => 0,
            'expenses'       => [],
        ];

        $payments = Payment::select('payments.*')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->with('invoice');

        if ($includeProfitBasedOn == 'invoiced_at') {
            $payments->where('invoices.invoiced_at', '>=', $fromDate)->where('invoices.invoiced_at', '<=', $toDate);
        } elseif ($includeProfitBasedOn == 'payment_date') {
            $payments->dateRange($fromDate, $toDate);
        }

        if ($companyProfileId) {
            $payments->where('invoices.company_id', $companyProfileId);
        }

        $payments = $payments->get();

        foreach ($payments as $payment) {
            $results['income'] += $payment->amount / $payment->invoice->exchange_rate;
        }

        $expenses = Expense::where('expensed_at', '>=', $fromDate)->where('expensed_at', '<=', $toDate)->with('category');

        if ($companyProfileId) {
            $expenses->where('company_id', $companyProfileId);
        }

        $expenses = $expenses->get();

        foreach ($expenses as $expense) {
            if (isset($results['expenses'][$expense->category->name])) {
                $results['expenses'][$expense->category->name] += $expense->amount;
            } else {
                $results['expenses'][$expense->category->name] = $expense->amount;
            }

            $results['total_expenses'] += $expense->amount;
        }

        foreach ($results['expenses'] as $category => $amount) {
            $results['expenses'][$category] = CurrencyFormatter::format($amount);
        }

        $results['net_income']     = CurrencyFormatter::format($results['income'] - $results['total_expenses']);
        $results['income']         = CurrencyFormatter::format($results['income']);
        $results['total_expenses'] = CurrencyFormatter::format($results['total_expenses']);

        ksort($results['expenses']);

        return $results;
    }
}
