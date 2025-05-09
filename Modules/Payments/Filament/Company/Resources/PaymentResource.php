<?php

namespace Modules\Payments\Filament\Company\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\ListPayments;
use Modules\Payments\Models\Payment;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.payment');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.payments');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.payments');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        //
                        // LEFT COLUMN: payable type + reference + status + amount
                        //
                        Form\Components\Group::make()
                            ->schema([
                                Section::make(trans('ip.payment'))
                                    ->schema([
                                        Form\Components\Group::make()->schema([
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
                                                    fn (int $value): string => ($invoice = Invoice::with('customer')->find($value))
                                                        ? "{$invoice->invoice_number} – {$invoice->customer?->company_name}"
                                                        : ''
                                                )
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->default(fn (?Payment $record) => $record?->invoice_id),
                                        ]),
                                        Grid::make()
                                            ->schema([
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
                                                    ->required(),
                                            ]),
                                    ]),
                            ]),

                        //
                        // RIGHT COLUMN: paid date + method
                        //
                        Form\Components\Group::make()
                            ->schema([
                                Section::make(trans('ip.payment_details'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('paid_at')
                                                    ->label(trans('ip.paid_at'))
                                                    ->required(),

                                                Select::make('payment_method_id')
                                                    ->label(trans('ip.payment_method'))
                                                    ->relationship('paymentMethod', 'payment_method_name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                            ]),
                                    ]),
                            ]),
                    ]),

                //
                // NOTES (collapsed)
                //
                Section::make(trans('ip.notes'))
                    ->schema([
                        MarkdownEditor::make('payment_note')
                            ->label(trans('ip.payment_note'))
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paid_at')
                    ->date('d-m-Y')
                    ->color(
                        fn (Payment $record) => optional($record->payable)->invoice_date_due && $record->paid_at > $record->payable->invoice_date_due
                        ? 'maroon'
                        : null
                    )
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('invoice.invoice_due_at')
                    ->label(trans('ip.due_date'))
                    ->since()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('invoice.invoice_number')
                    ->label(trans('ip.payment_reference'))
                    ->state(function (Payment $record) {
                        $invoice = $record->invoice;

                        return match (true) {
                            $invoice instanceof Invoice => $invoice->invoice_number,
                            default                     => null,
                        };
                    })
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('invoice.documentGroup.document_group_name')
                    ->limit(10)
                    ->label(trans('ip.invoice_group'))
                    ->hiddenFrom('xl')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('invoice.customer.company_name')
                    ->limit(10)
                    ->label(trans('ip.client'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('payment_amount')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('paymentMethod.payment_method_name')
                    ->limit(10)
                    ->label(trans('ip.payment_method'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * - payable (BelongsTo)
     * - paymentMethod (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
        ];
    }
}
