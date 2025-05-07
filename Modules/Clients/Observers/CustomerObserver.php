<?php

namespace Modules\Clients\Observers;

use Modules\Clients\Observers\CustomerObserver;

use Modules\Core\Support\Results\Clients;

use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    public function creating($model): void
    {
        if (empty($model->company_id)) {
            $companyId = session('current_company_id');
            if ($companyId) {
                $model->company_id = $companyId;
                Log::debug('CustomerObserver: Set company_id', ['company_id' => $companyId]);
            }
        }
    }
}
