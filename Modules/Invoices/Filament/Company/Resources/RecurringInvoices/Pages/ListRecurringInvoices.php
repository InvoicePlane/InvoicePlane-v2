<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\RecurringInvoiceResource;

class ListRecurringInvoices extends ListRecords
{
    protected static string $resource = RecurringInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(\Modules\Invoices\Services\RecurringInvoiceService::class)->createRecurringInvoice($data);
                })
                ->modalWidth('full'),
        ];
    }
}
