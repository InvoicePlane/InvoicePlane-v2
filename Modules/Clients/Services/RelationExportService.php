<?php

namespace Modules\Clients\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Clients\Exports\RelationsExport;
use Modules\Clients\Exports\RelationsLegacyExport;
use Modules\Clients\Models\Relation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RelationExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $relations   = Relation::query()->where('company_id', $companyId)->get();
        $fileName    = 'relations-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? RelationsLegacyExport::class : RelationsExport::class;

        return Excel::download(new $exportClass($relations), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }

    public function exportWithVersion(string $format = 'csv', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $relations   = Relation::query()->where('company_id', $companyId)->get();
        $fileName    = 'relations-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? RelationsLegacyExport::class : RelationsExport::class;

        return Excel::download(new $exportClass($relations), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
