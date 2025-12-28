<?php

namespace Modules\Payments\Services;

use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payments\Exports\PaymentsExport;
use Modules\Payments\Exports\PaymentsLegacyExport;
use Modules\Payments\Models\Payment;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentExportService
{
    public function export(string $format = 'xlsx'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        
        if (!$companyId) {
            abort(403, 'No company context available');
        }
        
        $payments    = Payment::query()
            ->where('company_id', $companyId)
            ->orderBy('paid_at', 'desc')
            ->limit(10000)
            ->get();
        $fileName    = 'payments-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? PaymentsLegacyExport::class : PaymentsExport::class;

        return Excel::download(new $exportClass($payments), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $payments    = Payment::query()->where('company_id', $companyId)->get();
        $fileName    = 'payments-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? PaymentsLegacyExport::class : PaymentsExport::class;

        return Excel::download(new $exportClass($payments), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }
}
