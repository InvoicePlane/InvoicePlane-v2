<?php

namespace Modules\Payments\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Payments\Exports\PaymentsExport;
use Modules\Payments\Models\Payment;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        $payments  = Payment::query()->where('company_id', $companyId)->get();
        $fileName  = 'payments-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(new PaymentsExport($payments), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
