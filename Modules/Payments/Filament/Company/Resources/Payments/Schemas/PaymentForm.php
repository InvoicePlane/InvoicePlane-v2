<?php

namespace Modules\Payments\Filament\Company\Resources\Payments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.payment_details'))
                            ->icon(Heroicon::OutlinedCreditCard)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('payment_number')
                                            ->label(trans('ip.payment_number'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->maxLength(255)
                                            ->columnSpan(4),

                                        Select::make('payment_status')
                                            ->label(trans('ip.payment_status'))
                                            ->prefixIcon(Heroicon::OutlinedCheckBadge)
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
                                            ->required()
                                            ->columnSpan(2),

                                        Select::make('payment_method')
                                            ->label(trans('ip.payment_method'))
                                            ->prefixIcon(Heroicon::OutlinedCreditCard)
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
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('payment_amount')
                                            ->label(trans('ip.payment_amount'))
                                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                                            ->numeric()
                                            ->required()
                                            ->dehydrated(true)
                                            ->default(fn (?Payment $record) => $record?->payment_amount)
                                            ->columnSpan(2),

                                        DatePicker::make('paid_at')
                                            ->label(trans('ip.paid_at'))
                                            ->prefixIcon(Heroicon::OutlinedCalendar)
                                            ->default(now())
                                            ->required()
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.invoice'))
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('invoice_id')
                                    ->label(trans('ip.invoice'))
                                    ->prefixIcon(Heroicon::OutlinedDocumentText)
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
                            ]),
                    ]),

                Section::make(trans('ip.notes'))
                    ->icon(Heroicon::OutlinedPencilSquare)
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
