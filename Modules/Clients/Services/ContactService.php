<?php

namespace Modules\Clients\Services;

use Illuminate\Support\Facades\DB;
use Modules\Clients\Events\ContactWasCreated;
use Modules\Clients\Events\ContactWasUpdated;
use Modules\Clients\Models\Contact;
use Modules\Core\Services\BaseService;

class ContactService extends BaseService
{
    public function model(): string
    {
        return Contact::class;
    }

    public function createContact(array $data): Contact
    {
        return DB::transaction(static function () use ($data) {
            $contact = Contact::query()->create([
                'relation_id' => $data['relation_id'],
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'default_to'  => $data['default_to'] ?? false,
                'default_cc'  => $data['default_cc'] ?? false,
                'default_bcc' => $data['default_bcc'] ?? false,
                'gender'      => $data['gender'] ?? null,
            ]);

            event(new ContactWasCreated($contact));

            return $contact;
        });
    }

    public function updateContact(Contact $contact, array $data): Contact
    {
        return DB::transaction(static function () use ($contact, $data) {
            $contact->update([
                'first_name'  => $data['first_name'] ?? $contact->first_name,
                'last_name'   => $data['last_name'] ?? $contact->last_name,
                'default_to'  => $data['default_to'] ?? $contact->default_to,
                'default_cc'  => $data['default_cc'] ?? $contact->default_cc,
                'default_bcc' => $data['default_bcc'] ?? $contact->default_bcc,
                'gender'      => $data['gender'] ?? $contact->gender,
            ]);

            event(new ContactWasUpdated($contact));

            return $contact;
        });
    }

    public function deleteContact(Contact $contact): Contact
    {
        return DB::transaction(static function () use ($contact) {
            $contact->delete();

            return $contact;
        });
    }
}
