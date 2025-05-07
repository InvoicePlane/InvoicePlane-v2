<?php

namespace Modules\Core\Filament\Admin\Resources\UserResource\Pages;

use Modules\Core\Filament\Admin\Resources\UserResource\Pages\EditUser;

use Modules\Core\Filament\Admin\Resources\UserResource;

use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
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
