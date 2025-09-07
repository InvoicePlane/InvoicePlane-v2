<?php

namespace Modules\Projects\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Projects\Exports\TasksExport;
use Modules\Projects\Exports\TasksLegacyExport;
use Modules\Projects\Models\Task;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $tasks       = Task::query()->where('company_id', $companyId)->get();
        $fileName    = 'tasks-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? TasksLegacyExport::class : TasksExport::class;

        return Excel::download(new $exportClass($tasks), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }

    public function exportWithVersion(string $format = 'csv', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $tasks       = Task::query()->where('company_id', $companyId)->get();
        $fileName    = 'tasks-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? TasksLegacyExport::class : TasksExport::class;

        return Excel::download(new $exportClass($tasks), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
