<?php

namespace Modules\Products\Filament\Resources\ProductFamilyResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Products\Filament\Resources\ProductFamilyResource;

class CreateProductFamily extends CreateRecord
{
    protected static string $resource = ProductFamilyResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
