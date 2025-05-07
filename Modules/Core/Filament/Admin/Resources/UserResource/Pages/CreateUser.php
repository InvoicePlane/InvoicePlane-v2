<?php

namespace Modules\Core\Filament\Admin\Resources\UserResource\Pages;

use Modules\Core\Filament\Admin\Resources\UserResource\Pages\CreateUser;

use Modules\Core\Filament\Admin\Resources\UserResource;

use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill(array_merge(
            $this->form->getRawState(),
            [
                'user_date_created'  => now()->toDateTimeString(),
                'user_date_modified' => now()->toDateTimeString(),
            ]
        ));
        parent::create($another);
    }
}
