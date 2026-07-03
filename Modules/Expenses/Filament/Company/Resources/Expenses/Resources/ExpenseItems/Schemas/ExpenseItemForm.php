<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExpenseItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_id')
                    ->required()
                    ->numeric(),
                TextInput::make('item_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('unit_id')
                    ->numeric()
                    ->default(null),
                DatePicker::make('added_at')
                    ->default(fn () => now()->toDateString()),
                TextInput::make('item_name')
                    ->default(null),
                Toggle::make('is_recurring')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1.0),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('discount')
                    ->numeric()
                    ->default(0.0),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('tax_1')
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_2')
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_total')
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->numeric()
                    ->default(0.0),
                Select::make('tax_rate_id')
                    ->relationship('tax_rate', 'name')
                    ->default(null),
                TextInput::make('tax_rate_2_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('display_order')
                    ->numeric()
                    ->default(null),
                TextInput::make('description')
                    ->default(null),
            ]);
    }
}
