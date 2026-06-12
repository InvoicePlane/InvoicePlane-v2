<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\RecurringInvoiceItemResource;

class CreateRecurringInvoiceItem extends CreateRecord
{
    protected static string $resource = RecurringInvoiceItemResource::class;
}
