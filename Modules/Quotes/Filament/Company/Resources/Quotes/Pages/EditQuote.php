<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Services\QuoteService;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        $this->callHook('beforeValidate');
        $data = $this->form->getState();
        $this->callHook('afterValidate');

        $data = $this->mutateFormDataBeforeSave($data);
        $this->callHook('beforeSave');

        $this->record = $this->handleRecordUpdate($this->getRecord(), $data);

        $this->callHook('afterSave');

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect) {
            $this->redirect($this->getRedirectUrl());
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(QuoteService::class)->updateQuote($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label(trans('ip.download_pdf'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => Notification::make()
                    ->title(trans('ip.not_yet_implemented'))
                    ->warning()
                    ->send()),

            Action::make('send_email')
                ->label(trans('ip.send_email'))
                ->icon(Heroicon::OutlinedEnvelope)
                ->action(fn () => Notification::make()
                    ->title(trans('ip.not_yet_implemented'))
                    ->warning()
                    ->send()),

            Action::make('convert_to_invoice')
                ->label(trans('ip.convert_to_invoice'))
                ->icon(Heroicon::OutlinedDocumentPlus)
                ->action(fn () => Notification::make()
                    ->title(trans('ip.not_yet_implemented'))
                    ->warning()
                    ->send()),

            Action::make('copy_quote')
                ->label(trans('ip.copy_quote'))
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->action(function (): void {
                    $copy = app(QuoteService::class)->duplicateQuote($this->getRecord());
                    $this->redirect(QuoteResource::getUrl('edit', ['record' => $copy]));
                }),

            DeleteAction::make()
                ->hidden(fn () => in_array($this->getRecord()->quote_status, [
                    QuoteStatus::APPROVED,
                    QuoteStatus::REJECTED,
                ])),
        ];
    }
}
