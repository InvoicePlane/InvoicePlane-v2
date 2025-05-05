<?php

namespace Modules\Projects\Filament\Company\Resources\ProjectResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Projects\Filament\Company\Resources\ProjectResource;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
