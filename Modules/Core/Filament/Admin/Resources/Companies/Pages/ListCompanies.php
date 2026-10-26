<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\Companies\CompanyResource;
use Modules\Core\Services\CompanyService;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data) {
                    app(CompanyService::class)->createCompany($data);
                })
                ->modalWidth('full'),
        ];
    }
}
