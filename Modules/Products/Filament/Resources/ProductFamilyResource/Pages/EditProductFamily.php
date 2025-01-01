<?php

namespace Modules\Products\Filament\Resources\ProductFamilyResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Products\Filament\Resources\ProductFamilyResource;

class EditProductFamily extends Page
{
    protected static string $resource = ProductFamilyResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
