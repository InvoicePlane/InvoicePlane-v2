<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems\Pages\CreateQuoteItem;
use Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems\Pages\EditQuoteItem;
use Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems\Schemas\QuoteItemForm;
use Modules\Quotes\Filament\Company\Resources\Quotes\Resources\QuoteItems\Tables\QuoteItemsTable;
use Modules\Quotes\Models\QuoteItem;

class QuoteItemResource extends Resource
{
    protected static ?string $model = QuoteItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $parentResource = QuoteResource::class;

    public static function form(Schema $schema): Schema
    {
        return QuoteItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuoteItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateQuoteItem::route('/create'),
            'edit'   => EditQuoteItem::route('/{record}/edit'),
        ];
    }
}
