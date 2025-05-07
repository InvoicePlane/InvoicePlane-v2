<?php

namespace Modules\Quotes\Events;

use Modules\Quotes\Events\QuoteWasUpdated;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuoteWasUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Quote $quote) {}
}
