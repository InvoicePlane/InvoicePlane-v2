<?php

namespace Modules\Invoices\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\Invoice;

class InvoiceService extends BaseService
{
    public function model(): string
    {
        return Invoice::class;
    }
}
