<?php

namespace Modules\Projects\Filament\Resources\ProjectResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Projects\Filament\Resources\ProjectResource;

class CreateProject extends Page
{
    protected static string $resource = ProjectResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
