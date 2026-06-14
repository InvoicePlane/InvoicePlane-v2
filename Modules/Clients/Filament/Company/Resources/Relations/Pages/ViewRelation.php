<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
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
        ];
    }
}
