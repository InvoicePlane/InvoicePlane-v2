<?php

namespace Modules\Core\Filament\Admin\Resources\TaxRates\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Filament\Admin\Resources\TaxRates\TaxRateResource;
use Modules\Core\Services\TaxRateService;

class EditTaxRate extends EditRecord
{
    protected static string $resource = TaxRateResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        $this->callHook('beforeValidate');
        $data = $this->form->getState();
        $this->callHook('afterValidate');

        $data = $this->mutateFormDataBeforeSave($data);
        $this->callHook('beforeSave');

        $this->record = $this->handleRecordUpdate($this->getRecord(), $data);

        $this->callHook('afterSave');

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect) {
            $this->redirect($this->getRedirectUrl());
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(TaxRateService::class)->updateTaxRate($record, $data);
    }
}
