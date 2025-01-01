<?php

namespace Modules\Projects\Filament\Resources\ProjectResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Projects\Filament\Resources\ProjectResource;

class EditProject extends Page
{
    protected static string $resource = ProjectResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
