<?php

namespace Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages;

use Modules\Core\Filament\Admin\Resources\TaxRateResource;

use Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages\ListTaxRates;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\TaxRateResource;

class ListTaxRates extends ListRecords
{
    protected static string $resource = TaxRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
