<?php

namespace Modules\Core\Filament\Resources\ImportResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Core\Filament\Resources\EmailTemplateResource;

class EditImport extends Page
{
    protected static string $resource = EmailTemplateResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
