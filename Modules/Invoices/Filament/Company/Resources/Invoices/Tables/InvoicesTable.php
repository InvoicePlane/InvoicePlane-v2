<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Support\DateHelpers;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_status')
                    ->badge()
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
                    ->label(trans('ip.invoice_due_at'))
                    ->color(fn ($state, $record) => $record?->due_intensity ?? 'secondary')
                    ->formatStateUsing(function ($state) {
                        if ( ! $state) {
                            return '-';
                        }
                        $days = now()->diffInDays($state, false);
                        if ($days < 0) {
                            return DateHelpers::formatSince($state, 3600);
                        }

                        return DateHelpers::formatDate($state);
                    })
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
                        ->mutateDataUsing(function (array $data, Invoice $record) {
                            $data['invoiceItems'] = $record->invoiceItems()->get()->map(function ($item) {
                                $product = $item->product;

                                return [
                                    'id'            => $item->id,
                                    'product_id'    => $item->product_id,
                                    'product_name'  => $product?->product_name ?? '',
                                    'item_name'     => $item->item_name,
                                    'quantity'      => $item->quantity,
                                    'price'         => $item->price,
                                    'discount'      => $item->discount,
                                    'subtotal'      => $item->subtotal,
                                    'tax_1'         => $item->tax_1,
                                    'tax_2'         => $item->tax_2,
                                    'tax_rate_id'   => $item->tax_rate_id,
                                    'tax_rate_2_id' => $item->tax_rate_2_id,
                                    'description'   => $item->description,
                                ];
                            })->toArray();

                            return $data;
                        })
                        ->action(function (Invoice $record, array $data) {
                            app(\Modules\Invoices\Services\InvoiceService::class)->updateInvoice($record, $data);
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
