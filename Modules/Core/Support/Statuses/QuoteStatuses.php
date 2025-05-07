<?php

namespace Modules\Core\Support\Statuses;

use Modules\Core\Support\Statuses\AbstractStatuses;

use Modules\Core\Support\Statuses\QuoteStatuses;

class QuoteStatuses extends AbstractStatuses
{
    protected static $statuses = [
        '0' => 'all_statuses',
        '1' => 'draft',
        '2' => 'is_sent',
        '3' => 'approved',
        '4' => 'rejected',
        '5' => 'canceled',
    ];
}
