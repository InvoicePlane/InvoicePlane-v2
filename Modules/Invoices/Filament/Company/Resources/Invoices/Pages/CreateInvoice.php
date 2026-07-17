<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Services\InvoiceService;

use function request;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

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

    public function mount(): void
    {
        parent::mount();

        if ($customerId = request()->integer('customer_id')) {
            $this->form->fill(['customer_id' => $customerId]);
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(InvoiceService::class)->createInvoice($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($customerId = request()->integer('customer_id')) {
            $data['customer_id'] = $customerId;
        }

        return $data;
    }
}
