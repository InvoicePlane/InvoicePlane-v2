<?php

namespace Modules\Projects\Filament\Company\Resources\TaskResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Projects\Filament\Company\Resources\TaskResource;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
