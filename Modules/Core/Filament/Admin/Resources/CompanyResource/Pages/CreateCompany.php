<?php

namespace Modules\Core\Filament\Admin\Resources\CompanyResource\Pages;

use Modules\Core\Filament\Admin\Resources\CompanyResource\Pages\CreateCompany;

use Modules\Core\Filament\Admin\Resources\CompanyResource;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Admin\Resources\CompanyResource;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;
}
