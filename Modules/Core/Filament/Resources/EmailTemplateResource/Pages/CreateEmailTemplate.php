<?php

namespace Modules\Core\Filament\Resources\EmailTemplateResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Core\Filament\Resources\EmailTemplateResource;

class CreateEmailTemplate extends Page
{
    protected static string $resource = EmailTemplateResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
