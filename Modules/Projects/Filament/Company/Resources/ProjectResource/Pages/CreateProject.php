<?php

namespace Modules\Projects\Filament\Company\Resources\ProjectResource\Pages;

use Modules\Projects\Filament\Company\Resources\ProjectResource\Pages\CreateProject;

use Modules\Core\Models\Company;

use Modules\Projects\Filament\Company\Resources\ProjectResource;

use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
