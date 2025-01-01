<?php

namespace Modules\Clients\Filament\Resources\ClientResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Clients\Filament\Resources\ClientResource;

class CreateClientNote extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill(array_merge(
            $this->form->getRawState(),
            [
                'client_date_created'  => now()->toDateTimeString(),
                'client_date_modified' => now()->toDateTimeString(),
            ]
        ));

        parent::create($another);
    }
}
