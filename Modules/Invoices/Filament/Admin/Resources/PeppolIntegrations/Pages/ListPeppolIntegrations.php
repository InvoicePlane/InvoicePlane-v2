<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\PeppolIntegrationResource;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Tables\PeppolIntegrationsTable;

class ListPeppolIntegrations extends ListRecords
{
    protected static string $resource = PeppolIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableConfiguration(): void
    {
        PeppolIntegrationsTable::configure($this->table);
    }
}
