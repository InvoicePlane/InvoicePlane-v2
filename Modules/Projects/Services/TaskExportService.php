<?php

namespace Modules\Projects\Services;

use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Projects\Exports\TasksExport;
use Modules\Projects\Exports\TasksLegacyExport;
use Modules\Projects\Models\Task;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskExportService
{
    public function export(string $format = 'xlsx'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if ( ! $companyId) {
            throw new RuntimeException('No company context available');
        }

        $tasks = Task::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'tasks-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? TasksLegacyExport::class : TasksExport::class;

        return Excel::download(new $exportClass($tasks), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if ( ! $companyId) {
            throw new RuntimeException('No company context available');
        }

        $tasks = Task::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'tasks-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? TasksLegacyExport::class : TasksExport::class;

        return Excel::download(new $exportClass($tasks), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }
}
