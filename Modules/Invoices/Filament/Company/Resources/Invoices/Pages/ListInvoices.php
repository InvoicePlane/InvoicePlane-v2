<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Services\InvoiceService;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('full')
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data, Action $action) {
                    $invoice = app(InvoiceService::class)->createInvoice($data);

                    if (filled($invoice->invoice_number)) {
                        $action->successNotificationTitle(
                            trans('ip.invoice_created_with_number', ['number' => $invoice->invoice_number])
                        );
                    }
                }),
        ];
    }
}
