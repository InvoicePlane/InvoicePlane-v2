<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplateResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\ReportTemplate\ReportTemplateResource;
use Modules\Core\Models\Company;
use Modules\Core\Services\ReportTemplateService;

class ListReportTemplates extends ListRecords
{
    protected static string $resource = ReportTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data) {
                    $company = Company::find(session('current_company_id'));
                    if ( ! $company) {
                        $company = auth()->user()->companies()->first();
                    }

                    $template = app(ReportTemplateService::class)->createTemplate(
                        $company,
                        $data['name'],
                        $data['template_type'],
                        []
                    );

                    $this->notify('success', trans('ip.template_created'));
                })
                ->modalWidth('full'),
        ];
    }
}
