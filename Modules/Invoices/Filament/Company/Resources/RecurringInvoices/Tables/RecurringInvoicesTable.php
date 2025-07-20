<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Helpers\EnumHelper;
use Modules\Invoices\Enums\RecurringFrequency;

class RecurringInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')->searchable()->sortable()->toggleable(),
                TextColumn::make('frequency')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(RecurringFrequency::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer.company_name')->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('start_at')
                    ->date()
                    ->since()->searchable()->sortable()->toggleable(),
                TextColumn::make('end_at')
                    ->date()
                    ->since()->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->mutateDataUsing(function (array $data, \Modules\Invoices\Models\RecurringInvoice $record) {
                            $data['recurringInvoiceItems'] = $record->recurringInvoiceItems()->get()->map(function ($item) {
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
                        ->action(function (\Modules\Invoices\Models\RecurringInvoice $record, array $data) {
                            app(\Modules\Invoices\Services\RecurringInvoiceService::class)
                                ->updateRecurringInvoice($record, $data);
                        })
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
