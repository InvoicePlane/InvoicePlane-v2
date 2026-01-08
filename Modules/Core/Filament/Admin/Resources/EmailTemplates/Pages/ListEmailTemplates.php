<?php

namespace Modules\Core\Filament\Admin\Resources\EmailTemplates\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\EmailTemplates\EmailTemplateResource;
use Modules\Core\Services\EmailTemplateService;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    $data['body'] ??= '';

                    return $data;
                })
                ->action(function (array $data) {
                    app(EmailTemplateService::class)->createEmailTemplate($data);
                })
                ->modalWidth('full'),
        ];
    }
}
