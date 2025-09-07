<?php

namespace Modules\Expenses\Services;

use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Expenses\Exports\ExpensesExport;
use Modules\Expenses\Exports\ExpensesLegacyExport;
use Modules\Expenses\Models\Expense;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExpenseExportService
{
    public function export(string $format = 'xlsx'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $expenses    = Expense::query()->where('company_id', $companyId)->get();
        $fileName    = 'expenses-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? ExpensesLegacyExport::class : ExpensesExport::class;

        return Excel::download(new $exportClass($expenses), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $expenses    = Expense::query()->where('company_id', $companyId)->get();
        $fileName    = 'expenses-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? ExpensesLegacyExport::class : ExpensesExport::class;

        return Excel::download(new $exportClass($expenses), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }
}
