<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
