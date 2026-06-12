<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RecurringInvoiceItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('item_id')
                    ->required()
                    ->numeric(),
                Select::make('tax_rate_id')
                    ->relationship('taxRate', 'name')
                    ->required()
                    ->default(0),
                Select::make('tax_rate_2_id')
                    ->relationship('taxRate2', 'name')
                    ->required()
                    ->default(0),
                TextInput::make('item_name')
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->default(0.0),
                TextInput::make('price')
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
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
                TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
