<?php

namespace Modules\Products\Filament\Resources\ProductUnitResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Products\Filament\Resources\ProductUnitResource;

class CreateProductUnit extends CreateRecord
{
    protected static string $resource = ProductUnitResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
