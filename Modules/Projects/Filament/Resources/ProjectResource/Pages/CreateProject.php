<?php

namespace Modules\Projects\Filament\Resources\ProjectResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Projects\Filament\Resources\ProjectResource;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
