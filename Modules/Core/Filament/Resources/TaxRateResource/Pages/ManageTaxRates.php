<?php

namespace Modules\Core\Filament\Resources\TaxRateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Core\Filament\Resources\TaxRateResource;

class ManageTaxRates extends ManageRecords
{
    protected static string $resource = TaxRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
