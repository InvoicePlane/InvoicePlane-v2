<?php

namespace Modules\Products\Filament\Resources\ProductFamilyResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Products\Filament\Resources\ProductFamilyResource;

class EditProductFamily extends EditRecord
{
    protected static string $resource = ProductFamilyResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
