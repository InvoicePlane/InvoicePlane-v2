<?php

namespace Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages;

use Modules\Core\Filament\Admin\Resources\TaxRateResource;

use Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages\CreateTaxRate;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Admin\Resources\TaxRateResource;

class CreateTaxRate extends CreateRecord
{
    protected static string $resource = TaxRateResource::class;
}
