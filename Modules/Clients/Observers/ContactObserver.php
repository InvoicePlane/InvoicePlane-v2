<?php

namespace Modules\Clients\Observers;

use Modules\Clients\Observers\ContactObserver;

use Modules\Clients\Models\Contact;

use Modules\Core\Support\Results\Clients;

use Illuminate\Support\Facades\Log;
use Modules\Clients\Models\Contact;

class ContactObserver
{
    public function creating(Contact $contact): void
    {
        Log::debug('Contact Observer: Creating contact', ['contact' => $contact]);

        if (empty($contact->company_id)) {
            $companyId = session('current_company_id');
            if ($companyId) {
                $contact->company_id = $companyId;
                Log::debug('Contact Observer: Set company_id for contact', ['company_id' => $companyId]);
            }
        }
    }
}
