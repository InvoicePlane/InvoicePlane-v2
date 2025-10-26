<?php

namespace Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Models\Company;
use Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource;
use Modules\ReportBuilder\Services\ReportTemplateService;

class ListReportTemplates extends ListRecords
{
    protected static string $resource = ReportTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data) {
                    $company = Company::find(session('current_company_id'));
                    if (!$company) {
                        $company = auth()->user()->companies()->first();
                    }

                    app(ReportTemplateService::class)->createTemplate(
                        $company,
                        $data['name'],
                        $data['template_type'],
                        []
                    );
                })
                ->modalWidth('full'),
        ];
    }
}
