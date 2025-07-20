<?php

namespace Modules\Payments\Filament\Company\Resources\Payments\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Payments\Models\Payment;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paid_at')
                    ->date('d-m-Y')
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
                TextColumn::make('invoice.documentGroup.name')
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
                    ->formatStateUsing(fn ($state) => trans('ip.' . $state))
                    ->limit(10)
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->mutateDataUsing(fn (array $data) => $data)
                        ->action(
                            fn (\Modules\Payments\Models\Payment $record, array $data) => app(\Modules\Payments\Services\PaymentService::class)
                                ->updatePayment($record, $data)
                        )
                        ->modalWidth('full'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
