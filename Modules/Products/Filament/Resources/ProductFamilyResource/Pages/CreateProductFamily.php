<?php

namespace Modules\Products\Filament\Resources\ProductFamilyResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Products\Filament\Resources\ProductFamilyResource;

class CreateProductFamily extends Page
{
    protected static string $resource = ProductFamilyResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
