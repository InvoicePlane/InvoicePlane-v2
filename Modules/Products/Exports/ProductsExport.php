<?php

namespace Modules\Products\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
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
            trans('ip.category_name'),
            trans('ip.product_unit'),
            trans('ip.product_sku'),
            trans('ip.product_name'),
            trans('ip.product_type'),
            trans('ip.product_price'),
            trans('ip.cost_price'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->productCategory?->category_name,
            $row->productUnit?->unit_name,
            $row->code,
            $row->product_name,
            $row->type?->label() ?? '',
            $row->price,
            $row->cost_price,
        ];
    }
}
