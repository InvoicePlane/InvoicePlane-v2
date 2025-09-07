<?php

namespace Modules\Clients\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Clients\Exports\ContactsExport;
use Modules\Clients\Exports\ContactsLegacyExport;
use Modules\Clients\Models\Contact;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContactExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $contacts    = Contact::query()->where('company_id', $companyId)->get();
        $fileName    = 'contacts-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? ContactsLegacyExport::class : ContactsExport::class;

        return Excel::download(new $exportClass($contacts), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }

    public function exportWithVersion(string $format = 'csv', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $contacts    = Contact::query()->where('company_id', $companyId)->get();
        $fileName    = 'contacts-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? ContactsLegacyExport::class : ContactsExport::class;

        return Excel::download(new $exportClass($contacts), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
