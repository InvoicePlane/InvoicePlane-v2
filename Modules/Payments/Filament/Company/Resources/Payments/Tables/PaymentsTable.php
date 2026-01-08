<?php

namespace Modules\Payments\Filament\Company\Resources\Payments\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Payments\Models\Payment;
use Modules\Payments\Services\PaymentService;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->label(trans('ip.payment_number'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->date('d-m-Y')
                    ->since()
                    ->color(
                        fn (Payment $record) => optional($record->invoice)->invoice_due_at && $record->paid_at > $record->invoice->invoice_due_at
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
                    ->state(fn (Payment $record) => $record->invoice?->invoice_number)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('payment_status')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('invoice.numbering.name')
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
                TextColumn::make('payment_method')
                    ->label(trans('ip.payment_method'))
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '')
                    ->limit(10)
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->action(function (Payment $record, array $data) {
                            app(PaymentService::class)->updatePayment($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->action(function (Payment $record, array $data) {
                            app(PaymentService::class)->deletePayment($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('paid_at', 'desc');
    }
}
