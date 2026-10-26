<?php

namespace Modules\Payments\Filament\Company\Resources\Payments\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\Permission;
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
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_PAYMENTS->value))
                        ->action(function (Payment $record, array $data) {
                            app(PaymentService::class)->updatePayment($record, $data);
                        })
                        ->modalWidth('full'),
                    Action::make('email_receipt')
                        ->visible(fn () => auth()->user()?->can(Permission::EMAIL_PAYMENTS->value))
                        ->label(trans('ip.send_email'))
                        ->action(function (Payment $record): void {}),
                    Action::make('refund')
                        ->visible(fn () => auth()->user()?->can(Permission::REFUND_PAYMENTS->value))
                        ->label(trans('ip.refund'))
                        ->action(function (Payment $record): void {}),
                    DeleteAction::make('delete')
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_PAYMENTS->value))
                        ->action(function (Payment $record, array $data) {
                            app(PaymentService::class)->deletePayment($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_PAYMENTS->value)),
                ]),
            ])->defaultSort('paid_at', 'desc');
    }
}
