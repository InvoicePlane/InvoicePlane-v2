<?php

namespace Modules\Core\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use Modules\Core\Filament\Resources\UserResource;

class EditUser extends ManageRecords
{
    protected static string $resource = UserResource::class;

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
