<?php

namespace Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages;

use Modules\Core\Filament\Admin\Resources\TaxRateResource;

use Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages\EditTaxRate;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\TaxRateResource;

class EditTaxRate extends EditRecord
{
    protected static string $resource = TaxRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
