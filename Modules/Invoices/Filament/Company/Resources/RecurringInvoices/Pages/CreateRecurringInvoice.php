<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\RecurringInvoiceResource;
use Modules\Invoices\Services\RecurringInvoiceService;

class CreateRecurringInvoice extends CreateRecord
{
    protected static string $resource = RecurringInvoiceResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        $this->callHook('beforeValidate');
        $data = $this->form->getState();
        $this->callHook('afterValidate');

        $data = $this->mutateFormDataBeforeCreate($data);
        $this->callHook('beforeCreate');

        $this->record = $this->handleRecordCreation($data);

        $this->form->model($this->getRecord())->saveRelationships();

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
        return app(RecurringInvoiceService::class)->createRecurringInvoice($data);
    }
}
