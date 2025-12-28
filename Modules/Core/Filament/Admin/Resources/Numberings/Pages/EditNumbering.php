<?php

namespace Modules\Core\Filament\Admin\Resources\Numberings\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Filament\Admin\Resources\Numberings\NumberingResource;
use Modules\Core\Models\Numbering;
use Modules\Core\Services\NumberingService;
use Throwable;

class EditNumbering extends EditRecord
{
    protected static string $resource = NumberingResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        $this->callHook('beforeValidate');
        $data = $this->form->getState();
        $this->callHook('afterValidate');

        $data = $this->mutateFormDataBeforeSave($data);
        $this->callHook('beforeSave');

        $result       = $this->handleRecordUpdate($this->getRecord(), $data);
        $this->record = $result->numbering;

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

    /**
     * @throws Throwable
     */
    protected function handleRecordUpdate(Numbering|Model $record, array $data): mixed
    {
        return app(NumberingService::class)->updateNumbering($record, $data);
    }
}
