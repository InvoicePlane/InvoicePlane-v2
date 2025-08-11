<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\DocumentGroupResource;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Services\DocumentGroupService;
use Throwable;

class EditDocumentGroup extends EditRecord
{
    protected static string $resource = DocumentGroupResource::class;

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

    /**
     * @throws Throwable
     */
    protected function handleRecordUpdate(DocumentGroup|Model $record, array $data): Model
    {
        return app(DocumentGroupService::class)->updateDocumentGroup($record, $data);
    }
}
