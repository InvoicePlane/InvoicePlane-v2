<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Tables;

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
use Modules\Core\Helpers\EnumHelper;
use Modules\Core\Support\DateHelpers;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteService;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_status')
                    ->label(trans('ip.quote_status'))
                    ->badge()
                    ->formatStateUsing(function (Quote $record) {
                        $status = EnumHelper::safeEnum(QuoteStatus::class, $record->quote_status);

                        return $status ? trans($status->label()) : '-';
                    })
                    ->color(function (Quote $record) {
                        $status = EnumHelper::safeEnum(QuoteStatus::class, $record->quote_status);

                        return $status?->color() ?? 'secondary';
                    }),
                TextColumn::make('quote_number')->searchable()->sortable()->toggleable(),
                TextColumn::make('prospect.company_name')
                    ->limit(10)
                    ->label(trans('ip.customer_name'))
                    ->searchable()->sortable()
                    ->toggleable(),
                TextColumn::make('quote_expires_at')
                    ->label(trans('ip.expires_at'))
                    ->color(fn ($state, $record) => $record?->expires_intensity ?? 'secondary')
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
                TextColumn::make('quote_total')->searchable()->sortable()->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_QUOTES->value))
                        ->action(function (Quote $record, array $data) {
                            app(QuoteService::class)->updateQuote($record, $data);
                        })
                        ->modalWidth('full'),
                    Action::make('duplicate')
                        ->visible(fn () => auth()->user()?->can(Permission::DUPLICATE_QUOTES->value))

                        ->label(trans('ip.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Quote $record): void {
                            app(QuoteService::class)->duplicateQuote($record);
                        })
                        ->successNotificationTitle(trans('ip.quote_duplicated')),
                    Action::make('download pdf')
                        ->visible(fn () => auth()->user()?->can(Permission::DOWNLOAD_QUOTES->value))
                        ->label(trans('ip.download_pdf'))
                        ->action(function (Quote $record) {
                            $pdfService = app(\Modules\Core\Services\PdfGenerationService::class);

                            return response()->streamDownload(
                                function () use ($pdfService, $record): void {
                                    echo $pdfService->quotePdf($record);
                                },
                                'quote-' . ($record->quote_number ?: $record->id) . '.pdf',
                            );
                        }),
                    Action::make('send email')
                        ->label(trans('ip.send_email'))
                        ->visible(fn () => auth()->user()?->can(Permission::EMAIL_QUOTES->value))

                        ->modalDescription('todo: make sure we can email the Quote through an action,
                            so need for modal anymore')
                        ->action(function (Quote $record): void {}),
                    Action::make('print')
                        ->label(trans('ip.print'))
                        ->icon('heroicon-o-printer')
                        ->visible(fn () => auth()->user()?->can(Permission::PRINT_QUOTES->value))
                        ->action(function (Quote $record): void {}),
                    Action::make('mark_sent')
                        ->label(trans('ip.mark_sent'))
                        ->icon('heroicon-o-check-circle')
                        ->visible(fn () => auth()->user()?->can(Permission::MARK_SENT_QUOTES->value))
                        ->action(function (Quote $record): void {}),
                    Action::make('approve')
                        ->label(trans('ip.approve'))
                        ->icon('heroicon-o-hand-thumb-up')
                        ->visible(fn () => auth()->user()?->can(Permission::APPROVE_QUOTES->value))
                        ->action(function (Quote $record): void {}),
                    Action::make('reject')
                        ->label(trans('ip.reject'))
                        ->icon('heroicon-o-hand-thumb-down')
                        ->visible(fn () => auth()->user()?->can(Permission::REJECT_QUOTES->value))
                        ->action(function (Quote $record): void {}),
                    Action::make('convert_to_invoice')
                        ->label(trans('ip.convert_to_invoice'))
                        ->icon('heroicon-o-arrow-right-circle')
                        ->visible(fn (Quote $record) => $record->quote_status !== QuoteStatus::CONVERTED
                            && auth()->user()?->can(Permission::CONVERT_TO_INVOICE_QUOTES->value))
                        ->requiresConfirmation()
                        ->action(function (Quote $record): void {
                            try {
                                app(QuoteService::class)->convertQuoteToInvoice($record);

                                \Filament\Notifications\Notification::make()
                                    ->title(trans('ip.quote_converted_to_invoice'))
                                    ->success()
                                    ->send();
                            } catch (InvalidArgumentException $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('archive')
                        ->label(trans('ip.archive'))
                        ->icon('heroicon-o-archive-box')
                        ->visible(fn () => auth()->user()?->can(Permission::ARCHIVE_QUOTES->value))
                        ->action(function (Quote $record): void {}),
                    DeleteAction::make('delete')
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_QUOTES->value))

                        ->action(function (Quote $quote) {
                            app(QuoteService::class)->deleteQuote($quote);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_QUOTES->value)),
                ]),
            ])
            ->defaultSort('quote_expires_at', 'asc');
    }
}
