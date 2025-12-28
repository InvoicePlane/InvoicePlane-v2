<?php

namespace Modules\Products\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Products\Models\Product;
use Modules\Core\Filament\Exporters\BaseExporter;

class ProductExporter extends BaseExporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('category_name')
                ->label(trans('ip.category_name'))
                ->formatStateUsing(fn ($state, Product $record) => $record->productCategory?->category_name ?? ''),
            ExportColumn::make('product_unit')
                ->label(trans('ip.product_unit'))
                ->formatStateUsing(fn ($state, Product $record) => $record->productUnit?->unit_name ?? ''),
            ExportColumn::make('code')
                ->label(trans('ip.product_sku')),
            ExportColumn::make('product_name')
                ->label(trans('ip.product_name')),
            ExportColumn::make('type')
                ->label(trans('ip.product_type'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('price')
                ->label(trans('ip.product_price')),
            ExportColumn::make('cost_price')
                ->label(trans('ip.cost_price')),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.product');
    }
}
