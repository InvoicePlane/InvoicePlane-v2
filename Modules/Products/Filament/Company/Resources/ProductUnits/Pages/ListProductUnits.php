<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnits\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Products\Filament\Company\Resources\ProductUnits\ProductUnitResource;
use Modules\Products\Services\ProductUnitService;

class ListProductUnits extends ListRecords
{
    protected static string $resource = ProductUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(ProductUnitService::class)->createProductUnit($data);
                })
                ->modalWidth('full'),
        ];
    }
}
