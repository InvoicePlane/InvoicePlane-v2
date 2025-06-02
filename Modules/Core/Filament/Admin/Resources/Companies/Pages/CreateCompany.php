<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Admin\Resources\Companies\CompanyResource;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;
}
