<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use InvalidArgumentException;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Enums\Permission;
use Modules\Core\Models\Numbering;
use Modules\Core\Support\DateHelpers;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Actions\EmailInvoiceAction;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceCopyService;
use Modules\Invoices\Services\InvoiceService;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Services\PaymentService;

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
                    ->sortable()
                    ->hiddenFrom('sm'),
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
            ->filters([
                SelectFilter::make('numbering_id')
                    ->label(trans('ip.numbering'))
                    ->options(fn (): array => Numbering::query()
                        ->where('type', NumberingType::INVOICE->value)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_INVOICES->value))
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
                    Action::make('copy')
                        ->visible(fn () => auth()->user()?->can(Permission::DUPLICATE_INVOICES->value))
                        ->label(trans('ip.copy_invoice'))
                        ->icon('heroicon-o-document-duplicate')
                        ->requiresConfirmation()
                        ->action(function (Invoice $record) {
                            app(InvoiceCopyService::class)->copy($record);
                            Notification::make()
                                ->title(trans('ip.invoice_copied'))
                                ->success()
                                ->send();
                        }),
                    Action::make('enter_payment')
                        ->label(trans('ip.enter_payment'))
                        ->icon('heroicon-o-banknotes')
                        ->visible(fn (Invoice $record) => auth()->user()?->can(Permission::CREATE_PAYMENTS->value)
                            && in_array($record->invoice_status, [
                                InvoiceStatus::SENT,
                                InvoiceStatus::VIEWED,
                                InvoiceStatus::PARTIALLY_PAID,
                                InvoiceStatus::OVERDUE,
                            ], true))
                        ->schema([
                            Placeholder::make('invoice')
                                ->label(trans('ip.invoice'))
                                ->content(fn (Invoice $record) => mb_trim(
                                    ($record->invoice_number ?? '#' . $record->id)
                                    . ' – ' . ($record->customer?->company_name ?? '')
                                )),
                            TextInput::make('payment_amount')
                                ->label(trans('ip.payment_amount'))
                                ->numeric()
                                ->minValue(0.01)
                                ->required(),
                            DatePicker::make('paid_at')
                                ->label(trans('ip.paid_at'))
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
                                ->native(false)
                                ->required(),
                        ])
                        ->fillForm(fn (Invoice $record) => [
                            'payment_amount' => app(PaymentService::class)->amountOwed($record),
                            'paid_at'        => now()->toDateString(),
                        ])
                        ->action(function (Invoice $record, array $data): void {
                            app(PaymentService::class)->enterInvoicePayment($record, $data);

                            Notification::make()
                                ->title(trans('ip.payment_recorded'))
                                ->success()
                                ->send();
                        }),
                    Action::make('download pdf')
                        ->visible(fn () => auth()->user()?->can(Permission::DOWNLOAD_INVOICES->value))
                        ->label(trans('ip.download_pdf'))
                        ->modalDescription(
                            'todo: make sure we can download the PDF of the Invoice through an action,
                            so need for modal anymore'
                        )
                        ->action(function (Invoice $record): void {}),
                    EmailInvoiceAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::EMAIL_INVOICES->value)),
                    DeleteAction::make('delete')
                        ->visible(fn (Invoice $record) => auth()->user()?->can(Permission::DELETE_INVOICES->value)
                            && $record->invoice_status !== InvoiceStatus::PAID)
                        ->action(function (Invoice $record, array $data) {
                            try {
                                app(InvoiceService::class)->deleteInvoice($record);
                            } catch (InvalidArgumentException $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_INVOICES->value)),
                ]),
            ])
            ->defaultSort('invoice_due_at', 'desc');
    }
}
