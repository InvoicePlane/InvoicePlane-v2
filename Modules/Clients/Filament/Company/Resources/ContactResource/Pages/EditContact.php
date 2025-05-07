<?php

namespace Modules\Clients\Filament\Company\Resources\ContactResource\Pages;

use Modules\Clients\Filament\Company\Resources\ContactResource\Pages\EditContact;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Clients\Filament\Company\Resources\ContactResource;

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
