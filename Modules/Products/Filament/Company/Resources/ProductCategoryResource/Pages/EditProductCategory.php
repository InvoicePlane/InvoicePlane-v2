<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource;

class EditProductCategory extends EditRecord
{
    protected static string $resource = ProductCategoryResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
