<?php

namespace Modules\Products\Services;

use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Products\Exports\ProductsExport;
use Modules\Products\Exports\ProductsLegacyExport;
use Modules\Products\Models\Product;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductExportService
{
    public function export(string $format = 'xlsx'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if ( ! $companyId) {
            throw new RuntimeException('No company context available');
        }

        $products = Product::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'products-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $version     = config('ip.export_version', 2);
        $exportClass = $version === 1 ? ProductsLegacyExport::class : ProductsExport::class;

        return Excel::download(new $exportClass($products), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }

    public function exportWithVersion(string $format = 'xlsx', int $version = 2): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        if ( ! $companyId) {
            throw new RuntimeException('No company context available');
        }

        $products = Product::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get();
        $fileName    = 'products-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportClass = $version === 1 ? ProductsLegacyExport::class : ProductsExport::class;

        return Excel::download(new $exportClass($products), $fileName, $format === 'csv' ? ExcelAlias::CSV : ExcelAlias::XLSX);
    }
}
