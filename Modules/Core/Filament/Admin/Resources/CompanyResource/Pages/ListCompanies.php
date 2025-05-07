<?php

namespace Modules\Core\Filament\Admin\Resources\CompanyResource\Pages;

use Modules\Core\Filament\Admin\Resources\CompanyResource;

use Modules\Core\Filament\Admin\Resources\CompanyResource\Pages\ListCompanies;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
