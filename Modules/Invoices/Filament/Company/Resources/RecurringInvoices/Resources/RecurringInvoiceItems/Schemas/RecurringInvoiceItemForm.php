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
                    ->label(fn (): string => trans('fields.item_id'))
                    ->required()
                    ->numeric(),
                Select::make('tax_rate_id')
                    ->label(fn (): string => trans('ip.tax_rate'))
                    ->relationship('taxRate', 'name')
                    ->required()
                    ->default(0),
                Select::make('tax_rate_2_id')
                    ->label(fn (): string => trans('fields.tax_rate_2'))
                    ->relationship('taxRate2', 'name')
                    ->required()
                    ->default(0),
                TextInput::make('item_name')
                    ->label(fn (): string => trans('fields.item_label'))
                    ->required(),
                TextInput::make('quantity')
                    ->label(fn (): string => trans('ip.quantity'))
                    ->numeric()
                    ->default(0.0),
                TextInput::make('price')
                    ->label(fn (): string => trans('ip.price'))
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('subtotal')
                    ->label(fn (): string => trans('ip.subtotal'))
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_1')
                    ->label(fn (): string => trans('fields.tax_1'))
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_2')
                    ->label(fn (): string => trans('fields.tax_2'))
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_total')
                    ->label(fn (): string => trans('ip.tax_total'))
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->label(fn (): string => trans('ip.total'))
                    ->numeric()
                    ->default(0.0),
                TextInput::make('display_order')
                    ->label(fn (): string => trans('fields.display_order'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('description')
                    ->label(fn (): string => trans('fields.item_description'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
