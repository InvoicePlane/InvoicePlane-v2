<?php

namespace Modules\Clients\Filament\Resources\ClientResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Clients\Filament\Resources\ClientResource;

class EditClientCustom extends EditRecord
{
    protected static string $resource = ClientResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill(array_merge(
            $this->form->getRawState(),
            [
                'client_date_modified' => now()->toDateTimeString(),
            ]
        ));

        parent::save();
    }
}
