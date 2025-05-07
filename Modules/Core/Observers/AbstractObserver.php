<?php

namespace Modules\Core\Observers;

use Illuminate\Support\Facades\Log;

class AbstractObserver
{
    public function creating($model): void
    {
        if (empty($model->company_id)) {
            $companyId = session('current_company_id');
            if ($companyId) {
                $model->company_id = $companyId;
                Log::debug('ClassName: Set company_id', ['company_id' => $companyId]);
            }
        }
    }
}
