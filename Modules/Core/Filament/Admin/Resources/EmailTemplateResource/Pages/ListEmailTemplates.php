<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource\Pages\ListEmailTemplates;

use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\EmailTemplateResource;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
