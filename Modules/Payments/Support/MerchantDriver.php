<?php

namespace Modules\Core\Support;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Support\Results\Invoices;

use Modules\Invoices\Models\Invoice;

abstract class MerchantDriver
{
    protected $isRedirect;

    abstract public function verify(Invoice $invoice);

    abstract public function getSettings();

    public function isRedirect()
    {
        return $this->isRedirect;
    }

    public function getName()
    {
        return class_basename($this);
    }

    public function getSetting($setting)
    {
        return config('ip.' . $this->getSettingKey($setting));
    }

    public function getSettingKey($setting)
    {
        return 'merchant_' . class_basename($this) . '_' . $setting;
    }
}
