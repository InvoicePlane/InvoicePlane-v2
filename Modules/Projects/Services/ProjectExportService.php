<?php

namespace Modules\Projects\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Projects\Exports\ProjectsExport;
use Modules\Projects\Exports\ProjectsLegacyExport;
use Modules\Projects\Models\Project;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $projects    = Project::query()->where('company_id', $companyId)->get();
        $fileName    = 'projects-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? ProjectsLegacyExport::class : ProjectsExport::class;

        return Excel::download(new $exportClass($projects), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }

    public function exportWithVersion(string $format = 'csv', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $projects    = Project::query()->where('company_id', $companyId)->get();
        $fileName    = 'projects-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? ProjectsLegacyExport::class : ProjectsExport::class;

        return Excel::download(new $exportClass($projects), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
