<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Pages\CreateExpenseItem;
use Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Pages\EditExpenseItem;
use Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Schemas\ExpenseItemForm;
use Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Tables\ExpenseItemsTable;
use Modules\Expenses\Models\ExpenseItem;

class ExpenseItemResource extends BaseResource
{
    protected static ?string $model = ExpenseItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $parentResource = ExpenseResource::class;

    public static function form(Schema $schema): Schema
    {
        return ExpenseItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpenseItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateExpenseItem::route('/create'),
            'edit'   => EditExpenseItem::route('/{record}/edit'),
        ];
    }
}
