<?php

namespace Modules\Clients\Filament\Company\Resources\Contacts\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Clients\Filament\Company\Resources\Contacts\ContactResource;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(\Modules\Clients\Services\ContactService::class)->createContact($data);
                })
                ->modalWidth('full'),
        ];
    }
}
