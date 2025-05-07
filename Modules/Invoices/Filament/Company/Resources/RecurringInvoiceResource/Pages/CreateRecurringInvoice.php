<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;

class CreateRecurringInvoice extends CreateRecord
{
    protected static string $resource = RecurringInvoiceResource::class;
}
