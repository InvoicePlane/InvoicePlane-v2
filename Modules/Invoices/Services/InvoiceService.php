<?php

namespace Modules\Invoices\Services;

use Modules\Invoices\Services\InvoiceService;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Services\BaseService;

use Modules\Core\Support\Results\Invoices;


class InvoiceService extends BaseService
{
    public function model(): string
    {
        return Invoice::class;
    }
}
