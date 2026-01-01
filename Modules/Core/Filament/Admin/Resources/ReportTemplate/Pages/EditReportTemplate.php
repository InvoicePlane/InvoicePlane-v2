<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplateResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Filament\Admin\Resources\ReportTemplate\ReportTemplateResource;
use Modules\Core\Services\ReportTemplateService;

class EditReportTemplate extends EditRecord
{
    protected static string $resource = ReportTemplateResource::class;

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
            DeleteAction::make()
                /* @phpstan-ignore-next-line */
                ->visible(fn () => ! $this->record->is_system)
                ->action(function () {
                    app(ReportTemplateService::class)->deleteTemplate($this->record);
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'template_type' => $data['template_type'],
            'is_active'     => $data['is_active'] ?? true,
        ]);

        return $record;
    }
}
