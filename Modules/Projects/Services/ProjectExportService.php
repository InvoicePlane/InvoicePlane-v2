<?php

namespace Modules\Projects\Services;

use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Projects\Exports\ProjectsExport;
use Modules\Projects\Exports\ProjectsLegacyExport;
use Modules\Projects\Models\Project;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectExportService
{
    public function export(string $format = 'xlsx'): BinaryFileResponse
    {
        $version = config('ip.export_version', 2);

        return $this->exportWithVersion($format, $version);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if (!$companyId) {
            throw new \RuntimeException('No company context available');
        }

        $projects    = Project::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'projects-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? ProjectsLegacyExport::class : ProjectsExport::class;

        return Excel::download(new $exportClass($projects), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }
}
