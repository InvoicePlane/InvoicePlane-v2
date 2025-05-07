<?php

namespace Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public function create(bool $another = false): void
    {
        parent::create($another);
    }
}
