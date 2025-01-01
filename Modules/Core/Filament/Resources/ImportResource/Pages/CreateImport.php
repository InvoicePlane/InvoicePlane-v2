<?php

namespace Modules\Core\Filament\Resources\ImportResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Resources\EmailTemplateResource;

class CreateImport extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
