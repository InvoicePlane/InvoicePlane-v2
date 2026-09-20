<?php

namespace Modules\Payments\Filament\Company\Resources\Payments\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;
use Modules\Payments\Services\PaymentService;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

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
        return app(PaymentService::class)->createPayment($data);
    }
}
