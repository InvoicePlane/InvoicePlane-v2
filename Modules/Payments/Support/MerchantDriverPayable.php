<?php

namespace App\IpModules\Merchant\Support;

use App\IpModules\Invoices\Models\Invoice;

abstract class MerchantDriverPayable extends MerchantDriver
{
    abstract public function pay(Invoice $invoice);
}
