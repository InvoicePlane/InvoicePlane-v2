<?php

namespace Modules\Core\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use Modules\Core\Filament\Resources\UserResource;

class CreateUser extends ManageRecords
{
    protected static string $resource = UserResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
