<?php

namespace Modules\Projects\Filament\Company\Resources\ProjectResource\Pages;

use Modules\Projects\Filament\Company\Resources\ProjectResource\Pages\EditProject;

use Modules\Core\Models\Company;

use Modules\Projects\Filament\Company\Resources\ProjectResource;

use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
