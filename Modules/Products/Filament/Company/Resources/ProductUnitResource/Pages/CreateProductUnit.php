<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductUnitResource;

use Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages\CreateProductUnit;

use Modules\Core\Models\Company;

use Filament\Resources\Pages\CreateRecord;
use Modules\Products\Filament\Company\Resources\ProductUnitResource;

class CreateProductUnit extends CreateRecord
{
    protected static string $resource = ProductUnitResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
