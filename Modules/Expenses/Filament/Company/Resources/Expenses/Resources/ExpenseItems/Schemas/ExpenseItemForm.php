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
                    ->label(fn (): string => trans('fields.company_id'))
                    ->required()
                    ->numeric(),
                TextInput::make('item_id')
                    ->label(fn (): string => trans('fields.item_id'))
                    ->numeric()
                    ->default(null),
                TextInput::make('unit_id')
                    ->label(fn (): string => trans('ip.unit'))
                    ->numeric()
                    ->default(null),
                DatePicker::make('added_at')->label(fn (): string => trans('fields.added_at')),
                TextInput::make('item_name')
                    ->label(fn (): string => trans('fields.item_label'))
                    ->default(null),
                Toggle::make('is_recurring')
                    ->label(fn (): string => trans('ip.recurring'))
                    ->required(),
                TextInput::make('quantity')
                    ->label(fn (): string => trans('ip.quantity'))
                    ->required()
                    ->numeric()
                    ->default(1.0),
                TextInput::make('price')
                    ->label(fn (): string => trans('ip.price'))
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('discount')
                    ->label(fn (): string => trans('ip.discount'))
                    ->numeric()
                    ->default(0.0),
                TextInput::make('subtotal')
                    ->label(fn (): string => trans('ip.subtotal'))
                    ->required()
                    ->numeric(),
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
                    ->relationship('tax_rate', 'name')
                    ->default(null),
                TextInput::make('tax_rate_2_id')
                    ->label(fn (): string => trans('fields.tax_rate_2'))
                    ->numeric()
                    ->default(null),
                TextInput::make('display_order')
                    ->label(fn (): string => trans('fields.display_order'))
                    ->numeric()
                    ->default(null),
                TextInput::make('description')
                    ->label(fn (): string => trans('fields.item_description'))
                    ->default(null),
            ]);
    }
}
