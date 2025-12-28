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
        $this->validateCompanyContext();
        
        $companyId = session('current_company_id');
        $payments = $this->getPayments($companyId);
        $version = config('ip.export_version', 2);
        
        return $this->downloadExport($payments, $format, $version);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $this->validateCompanyContext();
        
        $companyId = session('current_company_id');
        $payments = Payment::query()->where('company_id', $companyId)->get();
        
        return $this->downloadExport($payments, $format, $version);
    }

    protected function validateCompanyContext(): void
    {
        if (!session('current_company_id')) {
            abort(403, 'No company context available');
        }
    }

    protected function getPayments(int $companyId)
    {
        return Payment::query()
            ->where('company_id', $companyId)
            ->orderBy('paid_at', 'desc')
            ->limit(10000)
            ->get();
    }

    protected function downloadExport($payments, string $format, int $version): BinaryFileResponse
    {
        $fileName = $this->generateFileName($format);
        $exportClass = $this->getExportClass($version);
        $excelFormat = $this->getExcelFormat($format);

        return Excel::download(new $exportClass($payments), $fileName, $excelFormat);
    }

    protected function generateFileName(string $format): string
    {
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        return 'payments-' . now()->format('Y-m-d_H-i-s') . '.' . $extension;
    }

    protected function getExportClass(int $version): string
    {
        return $version === 1 ? PaymentsLegacyExport::class : PaymentsExport::class;
    }

    protected function getExcelFormat(string $format): string
    {
        return $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX;
    }
}
