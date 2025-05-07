<?php

namespace Modules\Projects\Filament\Company\Resources\TaskResource\Pages;

use Modules\Projects\Filament\Company\Resources\TaskResource;

use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\EditTask;

use Modules\Core\Models\Company;

use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
