<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Products\Filament\Company\Resources\ProductUnitResource;

class EditProductUnit extends EditRecord
{
    protected static string $resource = ProductUnitResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
