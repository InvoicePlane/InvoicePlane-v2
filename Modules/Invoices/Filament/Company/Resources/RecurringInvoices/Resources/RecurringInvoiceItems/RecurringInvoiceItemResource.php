<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\RecurringInvoiceResource;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Pages\CreateRecurringInvoiceItem;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Pages\EditRecurringInvoiceItem;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Schemas\RecurringInvoiceItemForm;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Tables\RecurringInvoiceItemsTable;
use Modules\Invoices\Models\RecurringInvoiceItem;

class RecurringInvoiceItemResource extends BaseResource
{
    protected static ?string $model = RecurringInvoiceItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $parentResource = RecurringInvoiceResource::class;

    public static function form(Schema $schema): Schema
    {
        return RecurringInvoiceItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecurringInvoiceItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateRecurringInvoiceItem::route('/create'),
            'edit'   => EditRecurringInvoiceItem::route('/{record}/edit'),
        ];
    }
}
