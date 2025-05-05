<?php

namespace Modules\Clients\Filament\Company\Resources\ContactResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Clients\Filament\Company\Resources\ContactResource;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
