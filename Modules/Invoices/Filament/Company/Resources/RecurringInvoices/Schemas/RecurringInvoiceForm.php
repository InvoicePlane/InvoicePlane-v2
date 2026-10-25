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
                TextInput::make('invoice_id')
                    ->label(fn (): string => trans('fields.invoice_id'))
                    ->required()
                    ->numeric(),
                TextInput::make('numbering_id')
                    ->label(fn (): string => trans('ip.numbering'))
                    ->numeric()
                    ->default(null),
                Select::make('frequency')
                    ->label(fn (): string => trans('fields.frequency'))
                    ->options(fn (): array => collect(RecurringFrequency::cases())
                        ->mapWithKeys(fn (RecurringFrequency $type): array => [$type->value => $type->label()])
                        ->all())
                    ->required(),
                DatePicker::make('start_at')
                    ->label(fn (): string => trans('ip.start_at'))
                    ->required(),
                DatePicker::make('end_at')->label(fn (): string => trans('ip.end_at')),
            ]);
    }
}
