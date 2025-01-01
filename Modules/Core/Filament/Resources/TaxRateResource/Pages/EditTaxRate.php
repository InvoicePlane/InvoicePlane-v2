<?php

namespace Modules\Core\Filament\Resources\TaxRateResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Core\Filament\Resources\TaxRateResource;

class EditTaxRate extends Page
{
    protected static string $resource = TaxRateResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
