<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource;
use Modules\Expenses\Services\ExpenseService;
use Throwable;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();
        $this->callHook('beforeValidate');
        $data = $this->form->getState();

        dd($data);

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

    /**
     * @throws Throwable
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(ExpenseService::class)->createInvoiceWithItems($data);
    }

    protected function afterCreate(): void
    {
        // You can hook here for events, notifications, logging, etc
    }
}
