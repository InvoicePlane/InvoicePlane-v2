<?php

namespace Modules\Core\Services\Migration\Migrators;

use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Address;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\AddressType;
use Modules\Core\Enums\CommunicationType;
use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\MigrationContext;
use Throwable;

class ClientMigrator implements EntityMigratorInterface
{
    public function name(): string
    {
        return 'clients';
    }

    public function label(): string
    {
        return 'Clients';
    }

    public function inspect(MigrationContext $context): array
    {
        $rows        = $context->getSourceTable('clients');
        $notes       = [];
        $willMigrate = 0;
        $unmappable  = 0;

        foreach ($rows as $row) {
            $name    = mb_trim((string) ($row['client_name'] ?? ''));
            $surname = mb_trim((string) ($row['client_surname'] ?? ''));

            if ($name === '' && $surname === '') {
                $unmappable++;
                $notes[] = "Client row #{$row['client_id']} has empty name and surname, will be skipped.";
            } else {
                $willMigrate++;
            }
        }

        return [
            'source_count' => $rows->count(),
            'will_migrate' => $willMigrate,
            'unmappable'   => $unmappable,
            'notes'        => $notes,
        ];
    }

    public function migrate(MigrationContext $context): array
    {
        $rows     = $context->getSourceTable('clients');
        $migrated = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $row) {
            $v1Id    = $row['client_id'] ?? null;
            $name    = mb_trim((string) ($row['client_name'] ?? ''));
            $surname = mb_trim((string) ($row['client_surname'] ?? ''));

            if ($name === '' && $surname === '') {
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('clients', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                $displayName = $name !== '' ? $name : $surname;
                $active      = (bool) ($row['client_active'] ?? true);
                $vatId       = ! empty($row['client_vat_id']) ? (string) $row['client_vat_id'] : null;
                $taxCode     = ! empty($row['client_tax_code']) ? (string) $row['client_tax_code'] : null;
                $website     = ! empty($row['client_web']) ? (string) $row['client_web'] : null;
                $language    = ! empty($row['client_language']) ? (string) $row['client_language'] : 'en';

                // Check existing relation by name/number
                $relation = Relation::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('company_name', $displayName)
                    ->first();

                if ( ! $relation) {
                    $relation = Relation::create([
                        'company_id'      => $context->getCompanyId(),
                        'company_name'    => $displayName,
                        'trading_name'    => $displayName,
                        'relation_type'   => RelationType::CUSTOMER,
                        'relation_status' => $active ? RelationStatus::ACTIVE : RelationStatus::INACTIVE,
                        'relation_number' => 'CST-' . mb_str_pad((string) ($v1Id ?? rand(100, 9999)), 5, '0', STR_PAD_LEFT),
                        'vat_number'      => $vatId,
                        'id_number'       => $taxCode,
                        'language'        => $language,
                        'registered_at'   => ! empty($row['client_date_created']) ? date('Y-m-d', strtotime($row['client_date_created'])) : date('Y-m-d'),
                    ]);
                    $context->recordCreated(Relation::class, $relation->id);
                }

                if ($v1Id !== null) {
                    $context->mapId('clients', $v1Id, $relation->id);
                }

                // Create or find Primary Contact
                $firstName = $name !== '' ? $name : 'Client';
                $lastName  = $surname !== '' ? $surname : ($name !== '' ? $name : 'Client');

                $contact = Contact::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('relation_id', $relation->id)
                    ->first();

                if ( ! $contact) {
                    $contact = Contact::create([
                        'company_id'  => $context->getCompanyId(),
                        'relation_id' => $relation->id,
                        'first_name'  => $firstName,
                        'last_name'   => $lastName,
                        'default_to'  => true,
                    ]);
                    $context->recordCreated(Contact::class, $contact->id);

                    // Link primary contact on relation
                    $relation->primary_contact_id = $contact->id;
                    $relation->saveQuietly();
                }

                // Communications (email, phone, mobile, fax)
                $this->syncContactCommunications($context, $contact, $row);

                // Address
                $this->syncRelationAddress($context, $relation, $row);

                $migrated++;
            } catch (Throwable $e) {
                $errors[] = "Failed to migrate client #{$v1Id} '{$name}': " . $e->getMessage();
                $skipped++;
            }
        }

        $context->log("Migrated {$migrated} clients ({$skipped} skipped).");

        return [
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    public function rollback(MigrationContext $context): int
    {
        $relationIds = $context->getCreatedIds(Relation::class);
        $contactIds  = $context->getCreatedIds(Contact::class);
        $addrIds     = $context->getCreatedIds(Address::class);
        $commIds     = $context->getCreatedIds(Communication::class);

        if ( ! empty($commIds)) {
            Communication::withoutGlobalScopes()->whereIn('id', $commIds)->delete();
        }
        if ( ! empty($addrIds)) {
            Address::withoutGlobalScopes()->whereIn('id', $addrIds)->delete();
        }
        if ( ! empty($contactIds)) {
            Contact::withoutGlobalScopes()->whereIn('id', $contactIds)->delete();
        }

        if (empty($relationIds)) {
            return 0;
        }

        return Relation::withoutGlobalScopes()
            ->whereIn('id', $relationIds)
            ->where('company_id', $context->getCompanyId())
            ->forceDelete();
    }

    protected function syncContactCommunications(MigrationContext $context, Contact $contact, array $row): void
    {
        $email  = mb_trim((string) ($row['client_email'] ?? ''));
        $phone  = mb_trim((string) ($row['client_phone'] ?? ''));
        $mobile = mb_trim((string) ($row['client_mobile'] ?? ''));
        $fax    = mb_trim((string) ($row['client_fax'] ?? ''));

        if ($email !== '') {
            $comm = Communication::create([
                'company_id'             => $context->getCompanyId(),
                'communicationable_type' => Contact::class,
                'communicationable_id'   => $contact->id,
                'communication_type'     => CommunicationType::EMAIL->value,
                'is_primary'             => true,
                'communication_value'    => $email,
            ]);
            $context->recordCreated(Communication::class, $comm->id);
        }

        if ($phone !== '') {
            $comm = Communication::create([
                'company_id'             => $context->getCompanyId(),
                'communicationable_type' => Contact::class,
                'communicationable_id'   => $contact->id,
                'communication_type'     => CommunicationType::PHONE->value,
                'is_primary'             => true,
                'communication_value'    => $phone,
            ]);
            $context->recordCreated(Communication::class, $comm->id);
        }

        if ($mobile !== '') {
            $comm = Communication::create([
                'company_id'             => $context->getCompanyId(),
                'communicationable_type' => Contact::class,
                'communicationable_id'   => $contact->id,
                'communication_type'     => CommunicationType::MOBILE->value,
                'is_primary'             => $phone === '',
                'communication_value'    => $mobile,
            ]);
            $context->recordCreated(Communication::class, $comm->id);
        }

        if ($fax !== '') {
            $comm = Communication::create([
                'company_id'             => $context->getCompanyId(),
                'communicationable_type' => Contact::class,
                'communicationable_id'   => $contact->id,
                'communication_type'     => CommunicationType::FAX->value,
                'is_primary'             => false,
                'communication_value'    => $fax,
            ]);
            $context->recordCreated(Communication::class, $comm->id);
        }
    }

    protected function syncRelationAddress(MigrationContext $context, Relation $relation, array $row): void
    {
        $addr1   = mb_trim((string) ($row['client_address_1'] ?? ''));
        $addr2   = mb_trim((string) ($row['client_address_2'] ?? ''));
        $city    = mb_trim((string) ($row['client_city'] ?? ''));
        $state   = mb_trim((string) ($row['client_state'] ?? ''));
        $zip     = mb_trim((string) ($row['client_zip'] ?? ''));
        $country = mb_trim((string) ($row['client_country'] ?? ''));

        if ($addr1 !== '' || $city !== '' || $zip !== '' || $country !== '') {
            $addr = Address::create([
                'company_id'        => $context->getCompanyId(),
                'addressable_type'  => Relation::class,
                'addressable_id'    => $relation->id,
                'address_type'      => AddressType::BILLING->value,
                'address_1'         => $addr1 ?: null,
                'address_2'         => $addr2 ?: null,
                'city'              => $city ?: 'Unknown',
                'state_or_province' => $state ?: null,
                'postal_code'       => $zip ?: '',
                'country'           => $country ?: 'US',
                'is_primary'        => true,
            ]);
            $context->recordCreated(Address::class, $addr->id);
        }
    }
}
