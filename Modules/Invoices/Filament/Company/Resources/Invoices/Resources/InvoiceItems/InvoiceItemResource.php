<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Pages\CreateInvoiceItem;
use Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Pages\EditInvoiceItem;
use Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Schemas\InvoiceItemForm;
use Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Tables\InvoiceItemsTable;
use Modules\Invoices\Models\InvoiceItem;

class InvoiceItemResource extends Resource
{
    protected static ?string $model = InvoiceItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $parentResource = InvoiceResource::class;

    public static function form(Schema $schema): Schema
    {
        return InvoiceItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoiceItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateInvoiceItem::route('/create'),
            'edit'   => EditInvoiceItem::route('/{record}/edit'),
        ];
    }
}
