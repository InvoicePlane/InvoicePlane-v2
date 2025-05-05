<?php

namespace Modules\Clients\Filament\Company\Resources\ContactResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Clients\Filament\Company\Resources\ContactResource;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
