<?php

namespace Modules\Clients\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Events\CustomerWasCreated;
use Modules\Clients\Events\CustomerWasUpdated;
use Modules\Clients\Models\Relation;
use Modules\Core\Services\BaseService;
use Throwable;

class RelationService extends BaseService
{
    public function model(): string
    {
        return Relation::class;
    }

    public function createRelation(array $data): Relation
    {
        DB::beginTransaction();

        try {
            $data['relation_number'] ??= $this->generateRelationNumber($data['relation_type']);
            $data['relation_status'] ??= RelationStatus::ACTIVE->value;

            $relation = Relation::query()->create([
                'primary_contact_id' => $data['primary_contact_id'] ?? null,
                'relation_type'      => $data['relation_type'],
                'relation_status'    => $data['relation_status'] ?? 'active',
                'relation_number'    => $data['relation_number'] ?? $this->generateRelationNumber($data['relation_type']),
                'company_name'       => $data['company_name'],
                'trading_name'       => $data['trading_name'] ?? null,
                'unique_name'        => $data['unique_name'] ?? null,
                'id_number'          => $data['id_number'] ?? null,
                'coc_number'         => $data['coc_number'] ?? null,
                'vat_number'         => $data['vat_number'] ?? null,
                'currency_code'      => $data['currency_code'] ?? null,
                'language'           => $data['language'] ?? null,
                'registered_at'      => $data['registered_at'] ?? now(),
            ]);

            if (isset($data['addresses']) && is_array($data['addresses'])) {
                $this->syncAddresses($relation, $data['addresses']);
            }

            if (isset($data['communications']) && is_array($data['communications'])) {
                $this->syncCommunications($relation, $data['communications']);
            }

            DB::commit();

            event(new CustomerWasCreated($relation));

            return $relation;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRelation(array $data, $relation): Relation
    {
        if ( ! $relation instanceof Relation) {
            $relation = Relation::query()->findOrFail($relation);
        }

        DB::beginTransaction();

        try {
            $relation->fill([
                'primary_contact_id' => $data['primary_contact_id'] ?? $relation->primary_contact_id,
                'relation_type'      => $data['relation_type'] ?? $relation->relation_type,
                'relation_status'    => $data['relation_status'] ?? $relation->relation_status,
                'company_name'       => $data['company_name'] ?? $relation->company_name,
                'trading_name'       => $data['trading_name'] ?? $relation->trading_name,
                'unique_name'        => $data['unique_name'] ?? $relation->unique_name,
                'id_number'          => $data['id_number'] ?? $relation->id_number,
                'coc_number'         => $data['coc_number'] ?? $relation->coc_number,
                'vat_number'         => $data['vat_number'] ?? $relation->vat_number,
                'currency_code'      => $data['currency_code'] ?? $relation->currency_code,
                'language'           => $data['language'] ?? $relation->language,
                'registered_at'      => $data['registered_at'] ?? $relation->registered_at,
            ]);

            $relation->save();

            if (isset($data['addresses']) && is_array($data['addresses'])) {
                $this->syncAddresses($relation, $data['addresses']);
            }

            if (isset($data['communications']) && is_array($data['communications'])) {
                $this->syncCommunications($relation, $data['communications']);
            }

            DB::commit();

            event(new CustomerWasUpdated($relation));

            return $relation;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function generateRelationNumber(string $relationType): string
    {
        $prefix       = RelationType::from($relationType)->prefix();
        $lastRelation = Relation::query()
            ->where('relation_type', $relationType)
            ->orderBy('id', 'desc')
            ->first();

        $nextId = $lastRelation ? ((int) Str::after($lastRelation->relation_number, $prefix) + 1) : 1;

        return $prefix . mb_str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    protected function syncAddresses(Relation $relation, array $addresses): void
    {
        $addressesToSync = [];

        foreach ($addresses as $addressData) {
            $addressesToSync[$addressData['address_id']] = [
                'type'       => $addressData['type'] ?? 'primary',
                'is_primary' => $addressData['is_primary'] ?? false,
            ];
        }

        $relation->addresses()->sync($addressesToSync);
    }

    protected function syncCommunications(Relation $relation, array $communications): void
    {
        $communicationsToSync = [];

        foreach ($communications as $index => $communicationData) {
            $communicationsToSync[] = [
                'communication_type'  => $communicationData['type'],
                'communication_value' => $communicationData['value'],
                'is_primary'          => $communicationData['is_primary'] ?? false,
            ];
        }

        $relation->communications()->delete();
        $relation->communications()->createMany($communicationsToSync);
    }
}
