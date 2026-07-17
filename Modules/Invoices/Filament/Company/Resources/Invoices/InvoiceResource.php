<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Filament\Company\Resources\Invoices\Schemas\InvoiceForm;
use Modules\Invoices\Filament\Company\Resources\Invoices\Tables\InvoicesTable;
use Modules\Invoices\Models\Invoice;

class InvoiceResource extends BaseResource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'edit'  => EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::VIEW_INVOICES->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::CREATE_INVOICES->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::EDIT_INVOICES->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::DELETE_INVOICES->value) ?? false;
    }
}
