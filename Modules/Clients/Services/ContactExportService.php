<?php

namespace Modules\Clients\Services;

use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Clients\Exports\ContactsExport;
use Modules\Clients\Exports\ContactsLegacyExport;
use Modules\Clients\Models\Contact;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContactExportService
{
    public function export(string $format = 'xlsx'): BinaryFileResponse
    {
        $version = config('ip.export_version', 2);

        return $this->exportWithVersion($format, $version);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $contacts    = Contact::query()->where('company_id', $companyId)->get();
        $fileName    = 'contacts-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? ContactsLegacyExport::class : ContactsExport::class;

        return Excel::download(new $exportClass($contacts), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }
}
