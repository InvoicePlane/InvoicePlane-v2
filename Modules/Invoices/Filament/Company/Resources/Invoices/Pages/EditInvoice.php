<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use Modules\Core\Enums\Permission;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Actions\EmailInvoiceAction;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Services\InvoiceService;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

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
        return app(InvoiceService::class)->updateInvoice($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label(trans('ip.preview'))
                ->icon(Heroicon::OutlinedEye)
                ->modalHeading(fn () => trans('ip.invoice') . ' ' . ($this->getRecord()->invoice_number ?? trans('ip.draft')))
                ->modalContent(fn () => new HtmlString(
                    app(InvoiceService::class)->renderHtml($this->getRecord())
                ))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(trans('ip.close'))
                ->slideOver()
                ->modalWidth('3xl'),

            Action::make('create_credit_note')
                ->label(trans('ip.create_credit_note'))
                ->icon(Heroicon::OutlinedDocumentMinus)
                ->visible(fn () => in_array($this->getRecord()->invoice_status, [
                    InvoiceStatus::SENT,
                    InvoiceStatus::PAID,
                    InvoiceStatus::PARTIALLY_PAID,
                ]))
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        $creditNote = app(InvoiceService::class)->createCreditNote($this->getRecord());
                    } catch (InvalidArgumentException $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title(trans('ip.credit_note_created'))
                        ->success()
                        ->send();

                    $this->redirect(InvoiceResource::getUrl('edit', ['record' => $creditNote]));
                }),

            Action::make('download_pdf')
                ->label(trans('ip.download_pdf'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => app(InvoiceService::class)->generatePdf($this->getRecord())),

            EmailInvoiceAction::make()
                ->visible(fn () => auth()->user()?->can(Permission::EMAIL_INVOICES->value)),

            Action::make('create_recurring')
                ->label(trans('ip.create_recurring'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->action(fn () => Notification::make()
                    ->title(trans('ip.not_yet_implemented'))
                    ->warning()
                    ->send()),

            Action::make('copy_invoice')
                ->label(trans('ip.copy_invoice'))
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->action(function (): void {
                    $original             = $this->getRecord();
                    $copy                 = $original->replicate(['invoice_number', 'invoice_status']);
                    $copy->invoice_status = InvoiceStatus::DRAFT;
                    $copy->invoice_number = null;
                    $copy->save();

                    foreach ($original->invoiceItems as $item) {
                        $copy->invoiceItems()->create($item->only([
                            'product_id', 'item_name', 'description',
                            'quantity', 'price', 'discount',
                            'tax_rate_id', 'tax_rate_2_id',
                        ]));
                    }

                    $this->redirect(InvoiceResource::getUrl('edit', ['record' => $copy]));
                }),

            DeleteAction::make()
                ->hidden(fn () => $this->getRecord()->invoice_status === InvoiceStatus::PAID
                    || $this->getRecord()->is_read_only),
        ];
    }
}
