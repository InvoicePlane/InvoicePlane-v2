<?php

namespace Modules\Invoices\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupService extends BaseService
{
    public function model(): string
    {
        return InvoiceGroup::class;
    }
}
