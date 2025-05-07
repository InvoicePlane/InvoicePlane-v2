<?php

namespace Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource;

use Modules\Core\Models\Company;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\CreateInvoice;

use Modules\Core\Support\Results\Invoices;

use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public function create(bool $another = false): void
    {
        parent::create($another);
    }
}
