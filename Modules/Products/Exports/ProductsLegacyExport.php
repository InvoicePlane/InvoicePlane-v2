<?php

namespace Modules\Products\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsLegacyExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $products;

    public function __construct(Collection $products)
    {
        $this->products = $products;
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            trans('ip.product_sku'),
            trans('ip.product_name'),
            trans('ip.product_price'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->code,
            $row->product_name,
            $row->price,
        ];
    }
}
