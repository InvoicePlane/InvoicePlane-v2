<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductCategoryResource;

use Modules\Core\Models\Company;

use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\ListProductCategories;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
