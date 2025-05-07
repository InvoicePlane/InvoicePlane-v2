<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\EditEmailTemplate;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;

use Filament\Resources\Pages\EditRecord;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
