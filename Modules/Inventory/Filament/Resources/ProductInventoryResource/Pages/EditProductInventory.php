<?php

namespace Modules\Inventory\Filament\Resources\ProductInventoryResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Inventory\Filament\Resources\ProductInventoryResource;

class EditProductInventory extends EditRecord
{
    protected static string $resource = ProductInventoryResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
