<?php

namespace Modules\Quotes\Services;

use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Quotes\Exports\QuotesExport;
use Modules\Quotes\Exports\QuotesLegacyExport;
use Modules\Quotes\Models\Quote;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuoteExportService
{
    public function export(string $format = 'xlsx'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if (!$companyId) {
            throw new \RuntimeException('No company context available');
        }

        $quotes      = Quote::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'quotes-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? QuotesLegacyExport::class : QuotesExport::class;

        return Excel::download(new $exportClass($quotes), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if (!$companyId) {
            throw new \RuntimeException('No company context available');
        }

        $quotes      = Quote::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'quotes-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? QuotesLegacyExport::class : QuotesExport::class;

        return Excel::download(new $exportClass($quotes), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }
}
