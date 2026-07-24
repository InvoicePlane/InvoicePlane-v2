<?php

namespace Modules\Core\Filament\Company\Resources\EmailTemplates\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Company\Resources\EmailTemplates\EmailTemplateResource;
use Modules\Core\Services\EmailTemplateService;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data) {
                    app(EmailTemplateService::class)->createEmailTemplate($data);
                })
                ->modalWidth('full'),
        ];
    }
}
