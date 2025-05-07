<?php

namespace Modules\Clients\Filament\Company\Resources\CustomerResource\Pages;

use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\CreateCustomer;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Clients\Filament\Company\Resources\CustomerResource;

use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
