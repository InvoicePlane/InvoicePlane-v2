<?php

namespace Modules\Core\Support;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Support\Results\Invoices;


abstract class MerchantDriverPayable extends MerchantDriver
{
    abstract public function pay(Invoice $invoice);
}
