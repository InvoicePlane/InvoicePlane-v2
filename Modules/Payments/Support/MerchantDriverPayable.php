<?php

namespace Modules\Core\Support;

use Modules\Invoices\Models\Invoice;

abstract class MerchantDriverPayable extends MerchantDriver
{
    abstract public function pay(Invoice $invoice);
}
