<?php

namespace Modules\Clients\Filament\Company\Resources\Contacts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Clients\Filament\Company\Resources\Contacts\ContactResource;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
