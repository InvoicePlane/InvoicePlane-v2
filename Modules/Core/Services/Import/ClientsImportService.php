<?php

namespace Modules\Core\Services\Import;

use Modules\Clients\Models\Address;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;

class ClientsImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_clients', 'ip_client_notes', 'ip_contacts'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['clients', 'contacts', 'addresses']);

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
            if (! empty($v1Client->client_address_1) || ! empty($v1Client->client_city)) {
                Address::create([
                    'company_id'     => $this->companyId,
                    'addressable_id' => $relation->id,
                    'addressable_type' => Relation::class,
                    'address_1'      => $v1Client->client_address_1 ?? null,
                    'address_2'      => $v1Client->client_address_2 ?? null,
                    'city'           => $v1Client->client_city ?? null,
                    'state'          => $v1Client->client_state ?? null,
                    'zip'            => $v1Client->client_zip ?? null,
                    'country'        => $v1Client->client_country ?? null,
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

            if (! $relationId) {
                continue;
            }

            Contact::create([
                'company_id'   => $this->companyId,
                'relation_id'  => $relationId,
                'contact_name' => $v1Contact->contact_name ?? 'Contact',
                'email'        => $v1Contact->contact_email ?? null,
                'phone'        => $v1Contact->contact_phone ?? null,
            ]);

            $this->stats['contacts']++;
        }
    }
}
