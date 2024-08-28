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
            Actions\CreateAction::make(),
        ];
    }
}
