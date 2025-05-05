<?php

namespace Modules\Products\Filament\Company\Resources\ItemResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Products\Filament\Company\Resources\ItemResource;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
