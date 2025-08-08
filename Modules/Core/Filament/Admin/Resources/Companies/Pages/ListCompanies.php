<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\Companies\CompanyResource;
use Modules\Core\Services\CompaniesService;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(CompaniesService::class)->createCompany($data);
                })
                ->modalWidth('full'),
        ];
    }
}
