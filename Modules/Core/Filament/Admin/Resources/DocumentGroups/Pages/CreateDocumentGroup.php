<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\DocumentGroupResource;
use Modules\Core\Services\DocumentGroupService;
use Throwable;

class CreateDocumentGroup extends CreateRecord
{
    protected static string $resource = DocumentGroupResource::class;

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

    /**
     * @throws Throwable
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(DocumentGroupService::class)->createDocumentGroup($data);
    }

    protected function afterCreate(): void
    {
        // optional event dispatch / audit log
    }
}
