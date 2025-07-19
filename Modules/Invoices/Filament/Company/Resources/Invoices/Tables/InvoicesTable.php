<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_status')
                    ->formatStateUsing(function ($state) {
                        $status = $state instanceof InvoiceStatus ? $state : InvoiceStatus::tryFrom($state);

                        return $status?->label();
                    })
                    ->color(function ($state) {
                        $status = $state instanceof InvoiceStatus ? $state : InvoiceStatus::tryFrom($state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer.company_name')->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('invoiced_at')
                    ->date()
                    ->since()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_due_at')
                    ->date()
                    ->since()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('invoice_total')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->mutateRecordDataUsing(function (array $data, Invoice $record) {
                            $data['invoiceItems'] = $record->invoiceItems()->get()->map(function ($item) {
                                return [
                                    'product_id' => $item->product_id,
                                    'quantity'   => $item->quantity,
                                    'price'      => $item->price,
                                    'discount'   => $item->discount,
                                    'subtotal'   => $item->subtotal,
                                ];
                            })->toArray();

                            return $data;
                        })
                        ->modalWidth('full'),
                    Action::make('download pdf')
                        ->label(trans('ip.download_pdf'))
                        ->modalDescription(
                            'todo: make sure we can download the PDF of the Invoice through an action,
                            so need for modal anymore'
                        )
                        ->action(function (Invoice $record): void {}),
                    Action::make('send email')
                        ->label(trans('ip.send_email'))
                        ->modalDescription('todo: make sure we can email the Invoice through an action,
                            so need for modal anymore')
                        ->action(function (Invoice $record): void {}),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('invoice_due_at', 'desc');
    }
}
