<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\CreateEmailTemplate;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
