<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations;

use Filament\Resources\Resource;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages\CreatePeppolIntegration;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages\EditPeppolIntegration;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages\ListPeppolIntegrations;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Schemas\PeppolIntegrationForm;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Tables\PeppolIntegrationsTable;
use Modules\Invoices\Models\PeppolIntegration;

class PeppolIntegrationResource extends Resource
{
    protected static ?string $model = PeppolIntegration::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $navigationLabel = 'Peppol Integrations';

    protected static ?string $navigationGroup = 'Settings';

    public static function getPages(): array
    {
        return [
            'index'  => ListPeppolIntegrations::make(),
            'create' => CreatePeppolIntegration::make(),
            'edit'   => EditPeppolIntegration::make(),
        ];
    }

    public static function getFormSchema(): array
    {
        return PeppolIntegrationForm::configure(
            app(\Filament\Schemas\Schema::class)
        )->getComponents();
    }

    public static function getTableSchema(): array
    {
        return PeppolIntegrationsTable::configure(
            app(\Filament\Tables\Table::class)
        )->getColumns();
    }
}
