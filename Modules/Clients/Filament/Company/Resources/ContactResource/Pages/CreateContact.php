<?php

namespace Modules\Clients\Filament\Company\Resources\ContactResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Clients\Filament\Company\Resources\ContactResource;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
