<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use InvalidArgumentException;
use Modules\Core\Enums\Permission;
use Modules\Core\Support\DateHelpers;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;

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
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_INVOICES->value)),
                    Action::make('download pdf')
                        ->visible(fn () => auth()->user()?->can(Permission::DOWNLOAD_INVOICES->value))
                        ->label(trans('ip.download_pdf'))
                        ->modalDescription(
                            'todo: make sure we can download the PDF of the Invoice through an action,
                            so need for modal anymore'
                        )
                        ->action(function (Invoice $record): void {}),
                    Action::make('send email')
                        ->visible(fn () => auth()->user()?->can(Permission::EMAIL_INVOICES->value))
                        ->label(trans('ip.send_email'))
                        ->action(function (Invoice $record): void {
                            app(InvoiceService::class)->sendInvoiceEmail($record);
                            // Optionally, show a notification
                            \Filament\Notifications\Notification::make()
                                ->title(trans('ip.email_sent'))
                                ->body(trans('ip.invoice_email_sent_successfully'))
                                ->success()
                                ->send();
                        }),
                    DeleteAction::make('delete')
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_INVOICES->value))
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
