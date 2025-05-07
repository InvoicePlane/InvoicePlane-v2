<?php

namespace Modules\Invoices\Services;

use Modules\Invoices\Services\InvoiceGroupService;

use Modules\Core\Services\BaseService;

use Modules\Core\Support\Results\Invoices;

use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupService extends BaseService
{
    public function model(): string
    {
        return InvoiceGroup::class;
    }
}
