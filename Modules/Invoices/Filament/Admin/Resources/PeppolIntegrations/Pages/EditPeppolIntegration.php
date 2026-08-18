<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\PeppolIntegrationResource;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Schemas\PeppolIntegrationForm;
use Modules\Invoices\Peppol\Services\PeppolManagementService;

class EditPeppolIntegration extends EditRecord
{
    protected static string $resource = PeppolIntegrationResource::class;

    protected function getFormSchema(): array
    {
        return PeppolIntegrationForm::configure(
            app(\Filament\Schemas\Schema::class)
        )->getComponents();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $service = app(PeppolManagementService::class);

        return $service->updateIntegration(
            $record,
            $data,
            $data['enabled'] ?? null
        );
    }
}
