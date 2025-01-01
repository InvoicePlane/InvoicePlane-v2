<?php

namespace Modules\Projects\Filament\Resources\TaskResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Projects\Filament\Resources\TaskResource;

class CreateTask extends Page
{
    protected static string $resource = TaskResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
