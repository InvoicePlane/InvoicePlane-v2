<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductCategoryResource;

use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\EditProductCategory;

use Modules\Core\Models\Company;

use Filament\Resources\Pages\EditRecord;

class EditProductCategory extends EditRecord
{
    protected static string $resource = ProductCategoryResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
