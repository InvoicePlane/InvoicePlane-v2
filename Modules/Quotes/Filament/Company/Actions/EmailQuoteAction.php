<?php

namespace Modules\Quotes\Filament\Company\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteService;

class EmailQuoteAction
{
    public static function make(): Action
    {
        return Action::make('email_quote')
            ->label(trans('ip.email_quote'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->schema(function (Quote $record) {
                $defaults = app(QuoteService::class)->resolveEmailDefaults($record);

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
            ->modalHeading(trans('ip.email_quote'))
            ->modalSubmitActionLabel(trans('ip.send_email'))
            ->action(function (Quote $record, array $data): void {
                app(QuoteService::class)->sendQuoteEmail(
                    $record,
                    $data['recipient'],
                    $data['subject'],
                    $data['body'],
                );

                Notification::make()
                    ->title(trans('ip.email_sent'))
                    ->body(trans('ip.quote_email_sent_successfully'))
                    ->success()
                    ->send();
            });
    }
}
