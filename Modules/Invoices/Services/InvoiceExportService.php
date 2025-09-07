<?php

namespace Modules\Invoices\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Invoices\Exports\InvoicesExport;
use Modules\Invoices\Exports\InvoicesLegacyExport;
use Modules\Invoices\Models\Invoice;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $invoices    = Invoice::query()->where('company_id', $companyId)->get();
        $fileName    = 'invoices-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? InvoicesLegacyExport::class : InvoicesExport::class;

        return Excel::download(new $exportClass($invoices), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }

    public function exportWithVersion(string $format = 'csv', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $invoices    = Invoice::query()->where('company_id', $companyId)->get();
        $fileName    = 'invoices-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? InvoicesLegacyExport::class : InvoicesExport::class;

        return Excel::download(new $exportClass($invoices), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
