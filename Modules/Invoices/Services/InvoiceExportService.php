<?php

namespace Modules\Invoices\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Invoices\Exports\InvoicesExport;
use Modules\Invoices\Models\Invoice;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        $invoices  = Invoice::query()->where('company_id', $companyId)->get();
        $fileName  = 'invoices-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(new InvoicesExport($invoices), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
