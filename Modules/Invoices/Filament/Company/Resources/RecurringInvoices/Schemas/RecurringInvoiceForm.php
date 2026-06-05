<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\Invoices\Enums\RecurringFrequency;

class RecurringInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'id')
                    ->required(),
                TextInput::make('invoice_id')
                    ->required()
                    ->numeric(),
                Select::make('numbering_id')
                    ->label(trans('ip.numbering'))
                    ->relationship('numbering', 'name')
                    ->default(null),
                Select::make('frequency')
                    ->options(RecurringFrequency::class)
                    ->required(),
                DatePicker::make('start_at')
                    ->required(),
                DatePicker::make('end_at'),
            ]);
    }
}
