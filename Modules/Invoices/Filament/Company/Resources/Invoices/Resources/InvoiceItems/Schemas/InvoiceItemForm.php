<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InvoiceItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_id')
                    ->required()
                    ->numeric(),
                Select::make('product_id')
                    ->relationship('product', 'id')
                    ->default(null),
                Select::make('task_id')
                    ->relationship('task', 'id')
                    ->default(null),
                Select::make('product_unit_id')
                    ->relationship('productUnit', 'id')
                    ->default(null),
                DatePicker::make('added_at')
                    ->default(fn () => now()->toDateString()),
                TextInput::make('item_name')
                    ->default(null),
                TextInput::make('product_unit')
                    ->default(null),
                Toggle::make('is_recurring'),
                TextInput::make('quantity')
                    ->numeric()
                    ->default(1.0),
                TextInput::make('price')
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('discount')
                    ->numeric()
                    ->default(0.0),
                TextInput::make('subtotal')
                    ->numeric()
                    ->default(0.0),
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
                    ->relationship('taxRate', 'name')
                    ->default(null),
                Select::make('tax_rate_2_id')
                    ->relationship('taxRate2', 'name')
                    ->default(null),
                TextInput::make('display_order')
                    ->numeric()
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
