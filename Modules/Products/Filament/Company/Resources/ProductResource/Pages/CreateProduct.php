<?php

namespace Modules\Products\Filament\Company\Resources\ProductResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductResource;

use Modules\Products\Filament\Company\Resources\ProductResource\Pages\CreateProduct;

use Modules\Core\Models\Company;

use Filament\Resources\Pages\CreateRecord;
use Modules\Products\Filament\Company\Resources\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
