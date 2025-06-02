<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

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
                    // Optionally set default values, e.g., invoice date
                    $data['invoiced_at']  = now();
                    $data['invoiceItems'] = [
                        ['product_id' => null, 'quantity' => 1, 'price' => 0, 'discount' => 0, 'subtotal' => 0],
                    ];

                    return $data;
                })
                ->action(function (array $data) {
                    app(InvoiceService::class)->createInvoice($data);
                }),
        ];
    }
}
