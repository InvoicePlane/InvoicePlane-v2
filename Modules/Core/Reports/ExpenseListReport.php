<?php

namespace Modules\Reports\Reports;

use Modules\Core\Support\DateFormatter;

use Modules\Expenses\Models\Expense;

use Modules\Core\Support\CurrencyFormatter;

use Modules\Core\Support\Results\Expenses;


class ExpenseListReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null, $categoryId = null, $vendorId = null)
    {
        $results = [
            'from_date' => DateFormatter::format($fromDate),
            'to_date'   => DateFormatter::format($toDate),
            'total'     => '0',
            'expenses'  => [],
        ];

        $expenses = Expense::defaultQuery()
            ->where('expensed_at', '>=', $fromDate)
            ->where('expensed_at', '<=', $toDate)
            ->orderBy('expensed_at', 'desc')
            ->orderBy('id', 'desc');

        if ($companyProfileId) {
            $expenses->where('company_id', $companyProfileId);
        }

        if ($categoryId) {
            $expenses->where('category_id', $categoryId);
        }

        if ($vendorId) {
            $expenses->where('vendor_id', $vendorId);
        }

        $expenses = $expenses->get();

        foreach ($expenses as $expense) {
            $results['expenses'][] = [
                'date'     => $expense->formatted_expense_date,
                'amount'   => $expense->formatted_amount,
                'tax'      => $expense->formatted_tax,
                'category' => $expense->category_name,
                'vendor'   => $expense->vendor_name,
                'customer' => $expense->client_name,
                'billed'   => $expense->has_been_billed,
            ];

            $results['total'] += $expense->amount;
        }

        $results['total'] = CurrencyFormatter::format($results['total']);

        return $results;
    }
}
