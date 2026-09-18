<?php

namespace Modules\Clients\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Clients\Models\Contact;

class ContactWasCreated
{
    use Dispatchable;

    public function __construct(public Contact $contact) {}
}
