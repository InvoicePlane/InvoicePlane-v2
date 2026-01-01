<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplateResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Filament\Admin\Resources\ReportTemplate\ReportTemplateResource;
use Modules\Core\Models\Company;
use Modules\Core\Services\ReportTemplateService;

class CreateReportTemplate extends CreateRecord
{
    protected static string $resource = ReportTemplateResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        $this->callHook('beforeValidate');
        $data = $this->form->getState();
        $this->callHook('afterValidate');

        $data = $this->mutateFormDataBeforeCreate($data);
        $this->callHook('beforeCreate');

        $this->record = $this->handleRecordCreation($data);

        $this->callHook('afterCreate');
        $this->rememberData();

        $this->getCreatedNotification()?->send();

        if ($another) {
            $this->form->model($this->getRecord()::class);
            $this->record = null;
            $this->fillForm();

            return;
        }

        $this->redirect($this->getRedirectUrl());
    }

    protected function handleRecordCreation(array $data): Model
    {
        $company = Company::find(session('current_company_id'));
        if ( ! $company) {
            $company = auth()->user()->companies()->first();
        }

        return app(ReportTemplateService::class)->createTemplate(
            $company,
            $data['name'],
            $data['template_type'],
            []
        );
    }
}
