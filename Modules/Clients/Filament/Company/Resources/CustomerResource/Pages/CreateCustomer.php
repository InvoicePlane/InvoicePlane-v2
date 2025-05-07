<?php

namespace Modules\Clients\Filament\Company\Resources\CustomerResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Clients\Filament\Company\Resources\CustomerResource;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
