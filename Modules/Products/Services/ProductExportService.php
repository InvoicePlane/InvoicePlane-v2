<?php

namespace Modules\Products\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Products\Exports\ProductsExport;
use Modules\Products\Exports\ProductsLegacyExport;
use Modules\Products\Models\Product;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $products    = Product::query()->where('company_id', $companyId)->get();
        $fileName    = 'products-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? ProductsLegacyExport::class : ProductsExport::class;

        return Excel::download(new $exportClass($products), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }

    public function exportWithVersion(string $format = 'csv', int $version = 2): BinaryFileResponse
    {
        $companyId   = session('current_company_id');
        $products    = Product::query()->where('company_id', $companyId)->get();
        $fileName    = 'products-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? ProductsLegacyExport::class : ProductsExport::class;

        return Excel::download(new $exportClass($products), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
