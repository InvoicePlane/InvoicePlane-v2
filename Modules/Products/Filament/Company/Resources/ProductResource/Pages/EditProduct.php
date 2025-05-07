<?php

namespace Modules\Products\Filament\Company\Resources\ProductResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductResource;

use Modules\Core\Models\Company;

use Modules\Products\Filament\Company\Resources\ProductResource\Pages\EditProduct;

use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
