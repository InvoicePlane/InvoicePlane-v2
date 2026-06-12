<?php

namespace Modules\Core\Services\Import;

use Modules\Clients\Models\Address;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;

class ClientsImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_clients', 'ip_contacts'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId  = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['clients', 'contacts', 'addresses', 'communications']);

        $this->importClients();
        $this->importContacts();

        return $this->stats;
    }

    private function importClients(): void
    {
        $clients = $this->getImportData('ip_clients');

        foreach ($clients as $v1Client) {
            $relation = Relation::create([
                'company_id'      => $this->companyId,
                'relation_type'   => 'customer',
                'relation_status' => ($v1Client->client_active ?? 1) == 1 ? 'active' : 'inactive',
                'relation_number' => $v1Client->client_name ?? 'CLIENT-' . $v1Client->client_id,
                'company_name'    => $v1Client->client_name,
                'vat_number'      => $v1Client->client_vat_id ?? null,
                'registered_at'   => now(),
            ]);

            $this->idMappings['clients'][$v1Client->client_id] = $relation->id;
            $this->stats['clients']++;

            // Import address if available
            if ( ! empty($v1Client->client_address_1) || ! empty($v1Client->client_city)) {
                Address::create([
                    'company_id'        => $this->companyId,
                    'address_type'      => 'billing',
                    'addressable_id'    => $relation->id,
                    'addressable_type'  => Relation::class,
                    'address_1'         => $v1Client->client_address_1 ?? null,
                    'address_2'         => $v1Client->client_address_2 ?? null,
                    'city'              => $v1Client->client_city ?? null,
                    'state_or_province' => $v1Client->client_state ?? null,
                    'postal_code'       => $v1Client->client_zip ?? null,
                    'country'           => $v1Client->client_country ?? null,
                ]);

                $this->stats['addresses']++;
            }
        }
    }

    private function importContacts(): void
    {
        $contacts = $this->getImportData('ip_contacts');

        foreach ($contacts as $v1Contact) {
            $relationId = $this->idMappings['clients'][$v1Contact->client_id] ?? null;

            if ( ! $relationId) {
                continue;
            }

            // Split contact name into first and last name
            $contactName = $v1Contact->contact_name ?? 'Contact';
            $nameParts   = explode(' ', $contactName, 2);
            $firstName   = $nameParts[0];
            $lastName    = $nameParts[1] ?? '';

            $contact = Contact::create([
                'company_id'  => $this->companyId,
                'relation_id' => $relationId,
                'first_name'  => $firstName,
                'last_name'   => $lastName,
            ]);

            $this->stats['contacts']++;

            // Import email as communication
            if ( ! empty($v1Contact->contact_email)) {
                Communication::create([
                    'company_id'             => $this->companyId,
                    'communicationable_id'   => $contact->id,
                    'communicationable_type' => Contact::class,
                    'is_primary'             => true,
                    'communication_type'     => 'email',
                    'communication_value'    => $v1Contact->contact_email,
                ]);

                $this->stats['communications']++;
            }

            // Import phone as communication
            if ( ! empty($v1Contact->contact_phone)) {
                Communication::create([
                    'company_id'             => $this->companyId,
                    'communicationable_id'   => $contact->id,
                    'communicationable_type' => Contact::class,
                    'is_primary'             => false,
                    'communication_type'     => 'phone',
                    'communication_value'    => $v1Contact->contact_phone,
                ]);

                $this->stats['communications']++;
            }
        }
    }
}
