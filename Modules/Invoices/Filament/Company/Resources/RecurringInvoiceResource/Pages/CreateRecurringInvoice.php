<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages;

use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\CreateRecurringInvoice;

use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;

use Modules\Core\Models\Company;

use Modules\Core\Support\Results\Invoices;

use Filament\Resources\Pages\CreateRecord;

class CreateRecurringInvoice extends CreateRecord
{
    protected static string $resource = RecurringInvoiceResource::class;
}
