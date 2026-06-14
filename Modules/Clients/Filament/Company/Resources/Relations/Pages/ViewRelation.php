<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Clients\Exceptions\RelationHasLinkedRecordsException;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Clients\Services\RelationService;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;

class ViewRelation extends ViewRecord
{
    protected static string $resource = RelationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_invoice')
                ->label(trans('ip.create_invoice'))
                ->icon('heroicon-o-document-plus')
                ->url(fn (): string => InvoiceResource::getUrl('create', [
                    'customer_id' => $this->getRecord()->id,
                ])),

            Action::make('create_quote')
                ->label(trans('ip.create_quote'))
                ->icon('heroicon-o-document-text')
                ->url(fn (): string => QuoteResource::getUrl('create', [
                    'customer_id' => $this->getRecord()->id,
                ])),

            EditAction::make(),

            DeleteAction::make()
                ->hidden(fn () => $this->clientHasLinkedRecords())
                ->action(function (): void {
                    try {
                        app(RelationService::class)->deleteRelation($this->getRecord());
                        $this->redirect(RelationResource::getUrl('index'));
                    } catch (RelationHasLinkedRecordsException $e) {
                        Notification::make()
                            ->title(trans('ip.cannot_delete_client_has_linked_records'))
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    private function clientHasLinkedRecords(): bool
    {
        $record = $this->getRecord();

        return $record->invoices()->withoutGlobalScopes()->exists()
            || $record->quotes()->withoutGlobalScopes()->exists()
            || $record->expenses()->withoutGlobalScopes()->exists()
            || $record->tasks()->withoutGlobalScopes()->exists()
            || $record->projects()->withoutGlobalScopes()->exists();
    }
}
