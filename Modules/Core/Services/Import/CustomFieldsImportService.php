<?php

namespace Modules\Core\Services\Import;

use Modules\Core\Enums\ModelType;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldValue;

class CustomFieldsImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_custom_fields', 'ip_custom_values'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId  = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['custom_fields', 'custom_field_values']);

        $this->importCustomFields();
        $this->importCustomFieldValues();

        return $this->stats;
    }

    private function importCustomFields(): void
    {
        $fields = $this->getImportData('ip_custom_fields');

        foreach ($fields as $v1Field) {
            $customField = CustomField::create([
                'company_id'          => $this->companyId,
                'custom_field_table'  => $v1Field->custom_field_table ?? 'invoices',
                'custom_field_label'  => $v1Field->custom_field_label ?? 'Custom Field',
                'custom_field_column' => $v1Field->custom_field_column ?? null,
            ]);

            $this->idMappings['custom_fields'][$v1Field->custom_field_id] = $customField->id;
            $this->stats['custom_fields']++;
        }
    }

    private function importCustomFieldValues(): void
    {
        $values = $this->getImportData('ip_custom_values');

        foreach ($values as $v1Value) {
            $customFieldId = $this->idMappings['custom_fields'][$v1Value->custom_field_id] ?? null;

            if ( ! $customFieldId) {
                continue;
            }

            $entityType = $v1Value->entity_type ?? 'invoice';
            $modelId    = $this->resolveModelId($entityType, $v1Value->entity_id ?? null);

            if ( ! $modelId) {
                continue;
            }

            CustomFieldValue::create([
                'company_id'         => $this->companyId,
                'custom_field_id'    => $customFieldId,
                'model_id'           => $modelId,
                'model_type'         => ModelType::fromString($entityType)->value,
                'custom_field_value' => $v1Value->custom_field_value ?? '',
            ]);

            $this->stats['custom_field_values']++;
        }
    }

    /**
     * Resolve the model ID from entity type and legacy ID.
     */
    private function resolveModelId(string $entityType, ?int $legacyId): ?int
    {
        if ($legacyId === null) {
            return null;
        }

        return match ($entityType) {
            'invoice' => $this->idMappings['invoices'][$legacyId] ?? null,
            'quote'   => $this->idMappings['quotes'][$legacyId] ?? null,
            'client'  => $this->idMappings['clients'][$legacyId] ?? null,
            'product' => $this->idMappings['products'][$legacyId] ?? null,
            default   => null,
        };
    }
}
