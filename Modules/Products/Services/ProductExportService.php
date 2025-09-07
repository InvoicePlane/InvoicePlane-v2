<?php

namespace Modules\Products\Services;

use Maatwebsite\Excel\Facades\Excel;
use Modules\Products\Exports\ProductsExport;
use Modules\Products\Models\Product;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductExportService
{
    public function export(string $format = 'csv'): BinaryFileResponse
    {
        $companyId = session('current_company_id');
        $products  = Product::query()->where('company_id', $companyId)->get();
        $fileName  = 'products-' . now()->format('Y-m-d_H-i-s') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(new ProductsExport($products), $fileName, $format === 'csv' ? Excel::CSV : Excel::XLSX);
    }
}
