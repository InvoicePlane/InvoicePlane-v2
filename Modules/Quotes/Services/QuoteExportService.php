<?php

namespace Modules\Quotes\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Quotes\Exports\QuotesExport;
use Modules\Quotes\Exports\QuotesLegacyExport;
use Modules\Quotes\Models\Quote;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuoteExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $quotes      = Quote::query()->where('company_id', $companyId)->get();
        $fileName    = 'quotes-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? QuotesLegacyExport::class : QuotesExport::class;

        return Excel::download(new $exportClass($quotes), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }

    public function exportWithVersion(string $format = 'csv', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $quotes      = Quote::query()->where('company_id', $companyId)->get();
        $fileName    = 'quotes-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? QuotesLegacyExport::class : QuotesExport::class;

        return Excel::download(new $exportClass($quotes), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
