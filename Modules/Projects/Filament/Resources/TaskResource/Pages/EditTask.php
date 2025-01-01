<?php

namespace Modules\Projects\Filament\Resources\TaskResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Projects\Filament\Resources\TaskResource;

class EditTask extends Page
{
    protected static string $resource = TaskResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
