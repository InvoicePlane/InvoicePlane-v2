<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\PeppolIntegrationResource;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Schemas\PeppolIntegrationForm;
use Modules\Invoices\Peppol\Services\PeppolManagementService;

class CreatePeppolIntegration extends CreateRecord
{
    protected static string $resource = PeppolIntegrationResource::class;

    protected function getFormSchema(): array
    {
        return PeppolIntegrationForm::configure(
            app(\Filament\Schemas\Schema::class)
        )->getComponents();
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $service = app(PeppolManagementService::class);

        return $service->createIntegration(
            $data['company_id'],
            $data['provider_name'],
            $data
        );
    }
}
