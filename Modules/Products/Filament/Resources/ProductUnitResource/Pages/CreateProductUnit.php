<?php

namespace Modules\Products\Filament\Resources\ProductUnitResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Products\Transformers\ProductUnitResource;

class CreateProductUnit extends Page
{
    protected static string $resource = ProductUnitResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
