<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\Companies\CompanyResource;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('full'),
        ];
    }
}
