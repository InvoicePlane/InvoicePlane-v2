<?php

namespace Modules\Products\Filament\Resources\ProductUnitResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Products\Filament\Resources\ProductUnitResource;

class EditProductUnit extends Page
{
    protected static string $resource = ProductUnitResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
