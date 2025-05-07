<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductUnitResource;

use Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages\EditProductUnit;

use Modules\Core\Models\Company;

use Filament\Resources\Pages\EditRecord;

class EditProductUnit extends EditRecord
{
    protected static string $resource = ProductUnitResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
