<?php

namespace Modules\Products\Filament\Resources\ProductResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Products\Filament\Resources\ProductResource;

class CreateProduct extends Page
{
    protected static string $resource = ProductResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
