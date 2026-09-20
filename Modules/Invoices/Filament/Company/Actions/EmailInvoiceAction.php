<?php

namespace Modules\Invoices\Filament\Company\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;

class EmailInvoiceAction
{
    public static function make(): Action
    {
        return Action::make('email_invoice')
            ->label(trans('ip.email_invoice'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->schema(function (Invoice $record) {
                $defaults = app(InvoiceService::class)->resolveEmailDefaults($record);

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
            ->modalHeading(trans('ip.email_invoice'))
            ->modalSubmitActionLabel(trans('ip.send_email'))
            ->action(function (Invoice $record, array $data): void {
                app(InvoiceService::class)->sendInvoiceEmail(
                    $record,
                    $data['recipient'],
                    $data['subject'],
                    $data['body'],
                );

                Notification::make()
                    ->title(trans('ip.email_sent'))
                    ->body(trans('ip.invoice_email_sent_successfully'))
                    ->success()
                    ->send();
            });
    }
}
