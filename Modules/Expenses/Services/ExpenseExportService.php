<?php

namespace Modules\Expenses\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Expenses\Exports\ExpensesExport;
use Modules\Expenses\Models\Expense;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseExportService
{
    public function export(string $format = 'csv'): StreamedResponse
    {
        $companyId = session('current_company_id');
        $expenses  = Expense::query()->where('company_id', $companyId)->get();
        $fileName  = 'expenses-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(new ExpensesExport($expenses), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
