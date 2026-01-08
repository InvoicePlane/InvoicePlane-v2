<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\InvoiceItemResource;

class CreateInvoiceItem extends CreateRecord
{
    protected static string $resource = InvoiceItemResource::class;
}
