<?php

namespace Modules\Products\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Products\Models\Product;

class ProductExporter extends Exporter
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

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
