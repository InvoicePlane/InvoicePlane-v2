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
            trans('ip.id'),
            trans('ip.company_id'),
            trans('ip.category_id'),
            trans('ip.unit_id'),
            trans('ip.type'),
            trans('ip.code'),
            trans('ip.product_name'),
            trans('ip.price'),
            trans('ip.cost_price'),
            trans('ip.tax_rate_id'),
            trans('ip.tax_rate_2_id'),
            trans('ip.product_tariff'),
            trans('ip.description'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->company_id,
            $row->category_id,
            $row->unit_id,
            $row->type,
            $row->code,
            $row->product_name,
            $row->price,
            $row->cost_price,
            $row->tax_rate_id,
            $row->tax_rate_2_id,
            $row->product_tariff,
            $row->description,
        ];
    }
}
