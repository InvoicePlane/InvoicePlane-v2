<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Pages\ListRecurringInvoices;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Schemas\RecurringInvoiceForm;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Tables\RecurringInvoicesTable;
use Modules\Invoices\Models\RecurringInvoice;

class RecurringInvoiceResource extends Resource
{
    protected static ?string $model = RecurringInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownOnSquareStack;

    protected static ?int $navigationSort = 20;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.recurring_invoice');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.recurring_invoices');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.recurring_invoices');
    }

    public static function form(Schema $schema): Schema
    {
        return RecurringInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecurringInvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecurringInvoices::route('/'),
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
