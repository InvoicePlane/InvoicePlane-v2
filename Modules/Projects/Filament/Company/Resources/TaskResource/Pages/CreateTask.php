<?php

namespace Modules\Projects\Filament\Company\Resources\TaskResource\Pages;

use Modules\Projects\Filament\Company\Resources\TaskResource;

use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\CreateTask;

use Modules\Core\Models\Company;

use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
