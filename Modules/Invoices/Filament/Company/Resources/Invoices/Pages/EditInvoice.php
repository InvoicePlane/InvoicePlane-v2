<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Invoices\Actions\SendInvoiceToPeppolAction;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Services\InvoiceService;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    public function mount($record): void
    {
        parent::mount($record);
    }

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
            Action::make('send_to_peppol')
                ->label(trans('ip.send_to_peppol'))
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    TextInput::make('customer_peppol_id')
                        ->label(trans('ip.customer_peppol_id'))
                        ->helperText(trans('ip.customer_peppol_id_helper'))
                        ->placeholder('BE:0123456789')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $action = app(SendInvoiceToPeppolAction::class);
                        $result = $action->execute($this->getRecord(), $data);
                        
                        Notification::make()
                            ->title(trans('ip.peppol_success_title'))
                            ->body(trans('ip.peppol_success_body', [
                                'document_id' => $result['document_id'] ?? 'N/A',
                            ]))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(trans('ip.peppol_error_title'))
                            ->body(trans('ip.peppol_error_body', ['error' => $e->getMessage()]))
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
