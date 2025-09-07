<?php

namespace Modules\Quotes\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Quotes\Exports\QuotesExport;
use Modules\Quotes\Models\Quote;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuoteExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        $quotes    = Quote::query()->where('company_id', $companyId)->get();
        $fileName  = 'quotes-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(new QuotesExport($quotes), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
