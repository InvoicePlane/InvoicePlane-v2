<?php

namespace Modules\Clients\Filament\Company\Resources\ContactResource\Pages;

use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\CreateContact;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Clients\Filament\Company\Resources\ContactResource;

use Filament\Resources\Pages\CreateRecord;
use Modules\Clients\Filament\Company\Resources\ContactResource;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
