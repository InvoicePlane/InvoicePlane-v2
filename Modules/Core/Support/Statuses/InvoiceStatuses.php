<?php

namespace Modules\Core\Support\Statuses;

use Modules\Core\Support\Statuses\InvoiceStatuses;

use Modules\Core\Support\Statuses\AbstractStatuses;

class InvoiceStatuses extends AbstractStatuses
{
    protected static $statuses = [
        '0' => 'all_statuses',
        '1' => 'draft',
        '2' => 'is_sent',
        '3' => 'paid',
        '4' => 'canceled',
    ];
}
