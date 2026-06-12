<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Products\Filament\Company\Resources\ProductCategories\ProductCategoryResource;
use Modules\Products\Services\ProductCategoryService;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(ProductCategoryService::class)->createProductCategory($data);
                })
                ->modalWidth('full'),
        ];
    }
}
