<?php

namespace Modules\Core\Filament\Resources\TaxRateResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Core\Filament\Resources\TaxRateResource;

class CreateTaxRate extends Page
{
    protected static string $resource = TaxRateResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
