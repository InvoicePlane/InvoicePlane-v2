<?php

namespace Modules\Projects\Filament\Company\Resources\TaskResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Projects\Filament\Company\Resources\TaskResource;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
