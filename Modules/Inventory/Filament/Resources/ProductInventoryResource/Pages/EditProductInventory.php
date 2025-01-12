<?php

namespace Modules\Products\Filament\Resources\ProductResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Products\Filament\Resources\ProductResource;

class EditProductInventory extends EditRecord
{
    protected static string $resource = ProductInventoryResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
