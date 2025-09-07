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
            trans('ip.id'),
            trans('ip.name'),
            trans('ip.sku'),
            trans('ip.price'),
            trans('ip.description'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->product_name,
            $row->code,
            $row->price,
            $row->description,
        ];
    }
}
