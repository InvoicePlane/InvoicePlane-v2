<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductCategoryResource;

use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\CreateProductCategory;

use Modules\Core\Models\Company;

use Filament\Resources\Pages\CreateRecord;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource;

class CreateProductCategory extends CreateRecord
{
    protected static string $resource = ProductCategoryResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
