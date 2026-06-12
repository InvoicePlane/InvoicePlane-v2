<?php

namespace Modules\Products\Filament\Company\Resources\Products\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Products\Filament\Company\Resources\Products\ProductResource;
use Modules\Products\Services\ProductService;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(ProductService::class)->createProduct($data);
                })->modalWidth('full'),
        ];
    }
}
