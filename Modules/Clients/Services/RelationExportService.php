<?php

namespace Modules\Clients\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Clients\Exports\RelationsExport;
use Modules\Clients\Models\Relation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RelationExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        $relations = Relation::query()->where('company_id', $companyId)->get();
        $fileName  = 'relations-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(new RelationsExport($relations), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
