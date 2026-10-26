<?php

namespace Modules\Invoices\Filament\Company\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;

class SendReminderAction
{
    /**
     * True when the invoice is eligible for a reminder: sent/overdue with a due
     * date in the past. Callers should AND this into their own ->visible()
     * closure alongside any permission check.
     */
    public static function isOverdue(Invoice $record): bool
    {
        return in_array($record->invoice_status, [InvoiceStatus::SENT, InvoiceStatus::OVERDUE], true)
            && $record->invoice_due_at !== null
            && $record->invoice_due_at->isPast();
    }

    public static function make(): Action
    {
        return Action::make('send_reminder')
            ->label(trans('ip.send_reminder'))
            ->icon(Heroicon::OutlinedBellAlert)
            ->disabled(fn (Invoice $record): bool => ! app(InvoiceService::class)->hasReminderRecipient($record))
            ->tooltip(fn (Invoice $record): ?string => app(InvoiceService::class)->hasReminderRecipient($record)
                ? null
                : trans('ip.customer_has_no_email'))
            ->schema(function (Invoice $record) {
                $defaults = app(InvoiceService::class)->resolveReminderDefaults($record);

                return [
                    TextInput::make('recipient')
                        ->label(trans('ip.recipient'))
                        ->email()
                        ->required()
                        ->default($defaults['recipient']),
                    TextInput::make('subject')
                        ->label(trans('ip.subject'))
                        ->required()
                        ->default($defaults['subject']),
                    Textarea::make('body')
                        ->label(trans('ip.body'))
                        ->required()
                        ->rows(10)
                        ->default($defaults['body']),
                ];
            })
            ->modalHeading(trans('ip.send_reminder'))
            ->modalSubmitActionLabel(trans('ip.send_reminder'))
            ->action(function (Invoice $record, array $data): void {
                app(InvoiceService::class)->sendReminder(
                    $record,
                    $data['recipient'],
                    $data['subject'],
                    $data['body'],
                );

                Notification::make()
                    ->title(trans('ip.reminder_sent'))
                    ->body(trans('ip.reminder_sent_successfully'))
                    ->success()
                    ->send();
            });
    }
}
