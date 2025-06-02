<?php

namespace Modules\Projects\Filament\Company\Resources\Projects\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Projects\Filament\Company\Resources\Projects\ProjectResource;
use Modules\Projects\Services\ProjectService;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProjectService::class)->updateProject($record, $data);
    }
}
