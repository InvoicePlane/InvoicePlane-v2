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
                    ->label(fn (): string => trans('fields.company_id'))
                    ->required()
                    ->numeric(),
                Select::make('product_id')
                    ->label(fn (): string => trans('fields.product_id'))
                    ->relationship('product', 'id')
                    ->default(null),
                Select::make('task_id')
                    ->label(fn (): string => trans('fields.task_id'))
                    ->relationship('task', 'id')
                    ->default(null),
                Select::make('product_unit_id')
                    ->label(fn (): string => trans('fields.product_unit_id'))
                    ->relationship('productUnit', 'id')
                    ->default(null),
                DatePicker::make('added_at')->label(fn (): string => trans('fields.added_at')),
                TextInput::make('item_name')
                    ->label(fn (): string => trans('fields.item_label'))
                    ->default(null),
                TextInput::make('product_unit')
                    ->label(fn (): string => trans('ip.product_unit'))
                    ->default(null),
                Toggle::make('is_recurring')->label(fn (): string => trans('ip.recurring')),
                TextInput::make('quantity')
                    ->label(fn (): string => trans('ip.quantity'))
                    ->numeric()
                    ->default(1.0),
                TextInput::make('price')
                    ->label(fn (): string => trans('ip.price'))
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('discount')
                    ->label(fn (): string => trans('ip.discount'))
                    ->numeric()
                    ->default(0.0),
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
                Select::make('tax_rate_id')
                    ->label(fn (): string => trans('ip.tax_rate'))
                    ->relationship('taxRate', 'name')
                    ->default(null),
                Select::make('tax_rate_2_id')
                    ->label(fn (): string => trans('fields.tax_rate_2'))
                    ->relationship('taxRate2', 'name')
                    ->default(null),
                TextInput::make('display_order')
                    ->label(fn (): string => trans('fields.display_order'))
                    ->numeric()
                    ->default(null),
                Textarea::make('description')
                    ->label(fn (): string => trans('fields.item_description'))
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
