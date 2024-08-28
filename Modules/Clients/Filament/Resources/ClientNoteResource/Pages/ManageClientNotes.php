<?php

namespace Modules\Clients\Filament\Resources\ClientNoteResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Clients\Filament\Resources\ClientNoteResource;

class ManageClientNotes extends ManageRecords
{
    protected static string $resource = ClientNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
