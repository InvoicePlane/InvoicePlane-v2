<?php

namespace Modules\Core\Filament\Admin\Resources\CompanyResource\Pages;

use Modules\Core\Filament\Admin\Resources\CompanyResource\Pages\EditCompany;

use Modules\Core\Filament\Admin\Resources\CompanyResource;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\CompanyResource;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
