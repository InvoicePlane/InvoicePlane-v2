<?php

namespace Modules\Clients\Filament\Resources\ClientResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Clients\Filament\Resources\ClientResource;

class ManageClients extends ManageRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data): void {
                    $data['client_date_created'] ??= now()->toDateTimeString();
                    $data['client_date_modified'] ??= now()->toDateTimeString();

                    $this->getModel()::create($data);
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Actions\EditAction::make()
                ->using(function ($record, array $data): void {
                    $data['client_date_modified'] = now()->toDateTimeString();

                    $record->update($data);
                }),
        ];
    }
}
