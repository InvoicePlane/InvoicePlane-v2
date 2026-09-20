<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Filament\Admin\Resources\Companies\CompanyResource;
use Modules\Core\Services\CompanyService;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

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
        return app(CompanyService::class)->createCompany($data);
    }
}
