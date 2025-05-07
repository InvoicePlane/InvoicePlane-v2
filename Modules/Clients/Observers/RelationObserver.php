<?php

namespace Modules\Clients\Observers;

use Modules\Clients\Observers\RelationObserver;

use Modules\Core\Support\Results\Clients;

use Modules\Clients\Models\Relation;

use Illuminate\Support\Facades\Log;
use Modules\Clients\Models\Relation;

class RelationObserver
{
    public function creating(Relation $relation): void
    {
        if (empty($relation->company_id)) {
            $companyId = session('current_company_id');
            if ($companyId) {
                $relation->company_id = $companyId;
                Log::debug('Relation Observer: Set company_id', ['company_id' => $companyId]);
            }
        }
    }
}
