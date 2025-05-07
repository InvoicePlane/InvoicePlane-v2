<?php

namespace Modules\Invoices\Observers;

use Modules\Invoices\Observers\InvoiceObserver;

use Modules\Core\Support\Results\Invoices;

use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    public function creating($model): void
    {
        if (empty($model->company_id)) {
            $companyId = session('current_company_id');
            if ($companyId) {
                $model->company_id = $companyId;
                Log::debug('InvoiceObserver: Set company_id', ['company_id' => $companyId]);
            }
        }
    }
}
