<?php

namespace Modules\Clients\Services;

use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Clients\Exports\RelationsExport;
use Modules\Clients\Exports\RelationsLegacyExport;
use Modules\Clients\Models\Relation;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RelationExportService
{
    public function export(string $format = 'xlsx'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if ( ! $companyId) {
            throw new RuntimeException('No company context available');
        }

        $relations = Relation::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'relations-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? RelationsLegacyExport::class : RelationsExport::class;

        return Excel::download(new $exportClass($relations), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if ( ! $companyId) {
            throw new RuntimeException('No company context available');
        }

        $relations = Relation::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'relations-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? RelationsLegacyExport::class : RelationsExport::class;

        return Excel::download(new $exportClass($relations), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }
}
