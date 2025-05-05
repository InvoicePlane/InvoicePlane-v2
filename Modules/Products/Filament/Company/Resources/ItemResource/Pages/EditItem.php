<?php

namespace Modules\Products\Filament\Company\Resources\ItemResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Products\Filament\Company\Resources\ItemResource;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
