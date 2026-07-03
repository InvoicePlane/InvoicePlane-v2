<?php

namespace Modules\Payments\Filament\Company\Resources\Payments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->columnSpanFull()
                    ->schema([
                        // LEFT COLUMN: invoice + customer
                        Schemas\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make(trans('ip.payment'))
                                    ->columns(1)
                                    ->schema([
                                        Grid::make()
                                            ->columns(1)
                                            ->schema([
                                                TextInput::make('payment_number')
                                                    ->label(trans('ip.payment_number'))
                                                    ->maxLength(255),

                                                Select::make('invoice_id')
                                                    ->label(trans('ip.invoice'))
                                                    ->getSearchResultsUsing(function (string $search): array {
                                                        return Invoice::with('customer')
                                                            ->where('invoice_number', 'like', "%{$search}%")
                                                            ->orWhereHas('customer', fn ($q) => $q->where('company_name', 'like', "%{$search}%"))
                                                            ->limit(50)
                                                            ->get()
                                                            ->pluck('invoice_number', 'id')
                                                            ->mapWithKeys(fn ($number, $id) => [
                                                                $id => "{$number} – " . Invoice::query()->find($id)->customer?->company_name,
                                                            ])
                                                            ->toArray();
                                                    })
                                                    ->getOptionLabelUsing(
                                                        fn (?int $value) => match (true) {
                                                            $value === null => '',
                                                            default         => ($invoice = Invoice::with('customer')->find($value))
                                                                ? "{$invoice->invoice_number} – {$invoice->customer?->company_name}"
                                                                : '',
                                                        }
                                                    )
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->default(fn (?Payment $record) => $record?->invoice_id),

                                                Placeholder::make('customer')
                                                    ->label(trans('ip.client'))
                                                    ->content(fn (?Payment $record) => $record?->customer?->company_name ?? '-'),
                                            ]),
                                    ]),
                            ]),

                        // RIGHT COLUMN: payment details
                        Schemas\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make(trans('ip.payment_details'))
                                    ->columnSpanFull()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('paid_at')
                                                    ->label(trans('ip.paid_at'))
                                                    ->default(fn () => now()->toDateString())
                                                    ->required(),

                                                Select::make('payment_method')
                                                    ->label(trans('ip.payment_method'))
                                                    ->options(
                                                        collect(PaymentMethod::cases())
                                                            ->mapWithKeys(fn (PaymentMethod $method) => [
                                                                $method->value => $method->label(),
                                                            ])
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->native(false)
                                                    ->required(),

                                                Select::make('payment_status')
                                                    ->label(trans('ip.payment_status'))
                                                    ->options(
                                                        collect(PaymentStatus::cases())
                                                            ->mapWithKeys(fn (PaymentStatus $s) => [
                                                                $s->value => trans($s->label()),
                                                            ])
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->native(false)
                                                    ->required(),

                                                TextInput::make('payment_amount')
                                                    ->label(trans('ip.payment_amount'))
                                                    ->numeric()
                                                    ->required()
                                                    ->dehydrated(true)
                                                    ->afterStateHydrated(fn ($component, $state) => $component->state($state))
                                                    ->default(fn (?Payment $record) => $record?->payment_amount),
                                            ]),
                                    ]),
                            ]),
                    ]),

                // NOTES (collapsed)
                Section::make(trans('ip.notes'))
                    ->columnSpanFull()
                    ->schema([
                        MarkdownEditor::make('note')
                            ->label(trans('ip.payment_note'))
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->collapsed(),
            ]);
    }
}
