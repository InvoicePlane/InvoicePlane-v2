<?php

namespace Modules\Clients\Filament\Resources\ClientCustomResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Clients\Filament\Resources\ClientCustomResource;

class ManageClientCustoms extends ManageRecords
{
    protected static string $resource = ClientCustomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
