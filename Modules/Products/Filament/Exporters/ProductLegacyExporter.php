<?php

namespace Modules\Products\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Core\Filament\Exporters\BaseExporter;
use Modules\Products\Models\Product;

class ProductLegacyExporter extends BaseExporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label(trans('ip.product_sku')),
            ExportColumn::make('product_name')
                ->label(trans('ip.product_name')),
            ExportColumn::make('price')
                ->label(trans('ip.product_price')),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.product');
    }
}
