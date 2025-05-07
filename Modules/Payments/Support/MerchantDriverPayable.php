<?php

namespace Modules\Payments\Support;

use Modules\Invoices\Models\Invoice;

abstract class MerchantDriverPayable extends MerchantDriver
{
    abstract public function pay(Invoice $invoice);
}
