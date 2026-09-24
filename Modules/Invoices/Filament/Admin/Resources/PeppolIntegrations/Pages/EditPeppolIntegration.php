<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\PeppolIntegrationResource;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Providers\ProviderFactory;
use Modules\Invoices\Peppol\Services\PeppolManagementService;

class EditPeppolIntegration extends EditRecord
{
    protected static string $resource = PeppolIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Inject the record's stored credential values into the form data before it fills —
     * they aren't real model columns, so they wouldn't otherwise reach the dynamic
     * credential fields (see PeppolIntegrationForm::getDynamicProviderFields()).
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var PeppolIntegration $record */
        $record = $this->getRecord();

        $providerClass = ProviderFactory::getProviderClass($record->provider_name);
        $keys          = array_diff($providerClass::settings(), $providerClass::managedSettingsKeys());

        foreach ($keys as $key) {
            $data[$key] = $record->getConfigValue($key);
        }

        return $data;
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
