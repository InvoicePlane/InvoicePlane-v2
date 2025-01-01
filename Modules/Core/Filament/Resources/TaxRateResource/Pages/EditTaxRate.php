<?php

namespace Modules\Core\Filament\Resources\TaxRateResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Resources\TaxRateResource;
use Modules\Core\Models\TaxRate;

class EditTaxRate extends EditRecord
{
    public static string $model = TaxRate::class;

    public ?array $data = [];

    protected static string $resource = TaxRateResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();
        $this->record->save();

        if ($shouldSendSavedNotification) {
            parent::save($shouldRedirect);
        }
    }
}
