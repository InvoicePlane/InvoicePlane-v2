<?php

namespace Modules\Projects\Filament\Company\Resources\TaskResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Projects\Filament\Company\Resources\TaskResource;
use Modules\Projects\Services\TaskService;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(TaskService::class)->updateTask($record, $data);
    }
}
